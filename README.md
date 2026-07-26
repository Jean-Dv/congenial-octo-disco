# Moon CMS

CMS modular para servidores privados de World of Warcraft, construido con Laravel 12 + Inertia/Vue. Incluye identidad, configuración multi-realm, aprovisionamiento automático de cuentas de juego, comunicación SOAP, temas y administración de módulos.

El repositorio contiene actualmente tres módulos: **Core** (obligatorio), **Public** (web pública) y **News** (administración y publicación de noticias).

## Índice

- [Arquitectura](#arquitectura)
- [Identidad: por qué dos "usuarios" separados](#identidad-por-qué-dos-usuarios-separados)
- [Multi-realm / multi-core](#multi-realm--multi-core)
- [Contraseñas de la cuenta de juego (SRP6)](#contraseñas-de-la-cuenta-de-juego-srp6)
- [Comunicación remota (SOAP hoy, lo que sea mañana)](#comunicación-remota-soap-hoy-lo-que-sea-mañana)
- [Aprovisionamiento asíncrono y seguridad de las colas](#aprovisionamiento-asíncrono-y-seguridad-de-las-colas)
- [Sistema de módulos](#sistema-de-módulos)
- [Sistema de temas](#sistema-de-temas)
- [Puesta en marcha](#puesta-en-marcha)
- [Verificación realizada](#verificación-realizada)
- [Qué falta / limitaciones conocidas](#qué-falta--limitaciones-conocidas)

---

## Arquitectura

Hexagonal (puertos y adaptadores), en 4 capas dentro de cada módulo:

```
Modules/Core/
├── Domain/            # Entidades, Value Objects, Puertos (interfaces). Cero Laravel.
├── Application/        # Casos de uso: orquestan el dominio a través de los puertos.
├── Infrastructure/      # Adaptadores concretos: Eloquent, SOAP, colas, hashing, mail.
└── Http/               # Controladores, Form Requests, middleware. El borde con el mundo.
```

La capa `Domain` de este módulo **no tiene ninguna dependencia de Laravel** (se verificó explícitamente, ver [Verificación realizada](#verificación-realizada)): es puro PHP, fácil de testear con Pest/PHPUnit sin arrancar el framework.

Además del módulo `Core`, hay un "shared kernel" en `src/Moon/` con dos kits que cualquier módulo (no solo Core) puede usar:

```
src/Moon/
├── ModuleKit/         # Contrato ModuleInterface + AbstractModule + ModuleManager
└── RemoteConsole/      # Puerto RemoteConsoleGatewayInterface + comandos + resultado
```

`src/Moon` es namespace propio (`Moon\`), separado de `Modules\Core\`, porque es infraestructura reutilizable por cualquier módulo futuro, no lógica de negocio del core.

## Identidad: por qué dos "usuarios" separados

El CMS **nunca** autentica el panel contra la tabla `account` de un reino. Hay dos identidades totalmente independientes:

| | Tabla | Motor | Hash |
|---|---|---|---|
| Usuario del panel | `users` (propia del CMS) | PostgreSQL | bcrypt (Laravel `Hash`) |
| Cuenta de juego | `account` (de cada reino) | MySQL (una por reino) | SRP6 (salt/verifier) |

Esto fue una decisión explícita: consultar la BD del core en cada login del panel sería un hueco de seguridad grande (expone la BD del juego a la superficie web del CMS). En su lugar:

- El **mismo usuario y contraseña** se capturan **una sola vez** en el registro.
- Con eso se genera un hash bcrypt para el panel **y**, por separado, las credenciales SRP6 para cada reino habilitado.
- `game_account_provisionings` (una fila por usuario+reino) rastrea si esa cuenta de juego ya se creó, sigue pendiente, o falló — así el dashboard puede mostrar el estado sin que el panel dependa de esa BD para nada más.
- Un reset de contraseña actualiza el panel **y** dispara el mismo recálculo hacia cada reino habilitado (confirmado como requisito).

## Multi-realm / multi-core

Un mismo panel administra **N reinos**, cada uno con su propio `CoreType`, su propia base de datos (host/puerto/credenciales) y sus propias credenciales SOAP. Nada de esto vive en `config/database.php`: se guarda (cifrado) en la tabla `realms` y se resuelve **en caliente** con `RealmConnectionFactory`, que registra una conexión Laravel dinámica por reino la primera vez que se usa.

Cores contemplados: TrinityCore, AzerothCore, CMaNGOS, MaNGOS Zero/One/Two, VMaNGOS, SkyFireEMU (`Modules\Core\Domain\Realm\ValueObjects\CoreType`).

**Soporte real, verificado, en esta entrega: TrinityCore y AzerothCore únicamente.** El resto queda con el contrato ya conectado (puedes crear el reino, elegir el core, guardar credenciales) pero la clase que calcula las credenciales lanza `PasswordHashStrategyNotImplementedException` con instrucciones de qué hacer, en vez de adivinar un esquema que podría estar mal y romper cuentas de juego reales. Motivo: el esquema de hash de la familia MaNGOS varía según el fork/revisión exacta (algunos usan `sha_pass_hash` SHA1, otros ya migraron a salt/verifier) y no hay una única fuente autoritativa válida para todos.

Para completar un core nuevo:
1. Confirma el esquema real de la tabla `account` de tu build.
2. Copia `Srp6PasswordHashStrategy` (si aplica) o escribe el algoritmo correcto, implementando `PasswordHashStrategyInterface`.
3. Regístralo en `PasswordHashStrategyResolver::resolve()`.
4. Si el layout de `account`/`account_access` difiere del de TrinityCore, haz lo mismo con `GameAccountGatewayInterface` en `GameAccountGatewayResolver`.

## Contraseñas de la cuenta de juego (SRP6)

`Srp6PasswordHashStrategy` implementa el algoritmo real usado por el cliente 3.3.5a (compartido, byte a byte, por TrinityCore y AzerothCore):

```
h1 = SHA1(UPPER(username) + ":" + UPPER(password))
x  = SHA1(salt || h1)              (interpretado como entero little-endian)
v  = g^x mod N                      (N y g son las constantes GruntSRP6)
```

**Esto no se dejó como una suposición.** Se verificó de tres formas independientes antes de escribirlo:

1. Se leyó el código fuente real de `TrinityCore/TrinityCore` (`src/common/Cryptography/Authentication/SRP6.cpp`, `src/server/game/Accounts/AccountMgr.cpp`) para confirmar N, g, k=3 y el uso de `Utf8ToUpperOnlyLatin`.
2. Se confirmó que `azerothcore/azerothcore-wotlk` usa exactamente el mismo N/g (por eso una sola clase sirve para ambos cores).
3. Se instaló el paquete npm `trinitycore-srp6` (implementación de referencia independiente) y se comparó su salida contra la de este código PHP, con el mismo username/password/salt fijos: **el verifier resultante fue byte-por-byte idéntico** en ambas implementaciones.

Límite de 16 caracteres ASCII en usuario y contraseña: no es arbitrario, es el límite real del cliente 3.3.5a — se valida tanto en el formulario (`RegisterUserRequest`) como dentro de la propia estrategia (defensa en profundidad).

## Comunicación remota (SOAP hoy, lo que sea mañana)

`Moon\RemoteConsole\Contracts\RemoteConsoleGatewayInterface` es el puerto único:

```php
interface RemoteConsoleGatewayInterface {
    public function execute(RemoteCommandInterface $command, RemoteConsoleConnection $connection): RemoteCommandResult;
}
```

Hoy hay una sola implementación, `SoapRemoteConsoleGateway` (PHP `SoapClient` sin WSDL, tal como documentan TrinityCore/AzerothCore: método remoto `executeCommand`, autenticación con una cuenta GM rango 3+). El binding vive en **un solo lugar**, `CoreServiceProvider::register()`:

```php
$this->app->bind(RemoteConsoleGatewayInterface::class, SoapRemoteConsoleGateway::class);
```

El día que un core hable gRPC o REST: se escribe `GrpcRemoteConsoleGateway implements RemoteConsoleGatewayInterface`, se cambia esa única línea, y ningún caso de uso ni controlador se entera del cambio.

Alcance SOAP de esta entrega (confirmado con el negocio): `server info` (uptime/versión/online), `character rename`, `character customize`, `kick player`. Todo lo demás (crear cuenta, cambiar password, gmlevel, borrar cuenta) va por **conexión SQL directa** (`GameAccountGatewayInterface`), no por SOAP.

## Aprovisionamiento asíncrono y seguridad de las colas

Al registrarse, se crea una cuenta de juego **en todos los reinos habilitados**, en segundo plano (colas Redis), con reintentos: 5 intentos, backoff `5s, 15s, 30s, 60s, 120s`. El usuario puede entrar al panel de inmediato aunque algún reino siga "pendiente" (confirmado como requisito) — el dashboard muestra el estado por reino.

Detalle de seguridad deliberado: **la contraseña en texto plano nunca se pone en cola.** `RegisterUserUseCase` y `ResetPasswordUseCase` calculan el salt/verifier SRP6 de forma síncrona, en el propio request (es una operación de CPU, no de red), y solo esas credenciales *ya derivadas* viajan al Job. Además, `ProvisionGameAccountJob` y `SyncPasswordToRealmJob` implementan `ShouldBeEncrypted`, así que ese payload también va cifrado con `APP_KEY` mientras espera en Redis.

Las credenciales de conexión de cada reino (contraseñas de MySQL y de SOAP, además de la llave SSH y su passphrase cuando aplique) se guardan cifradas en Postgres mediante casts `encrypted:array` de Laravel.

## Sistema de módulos

Todo en este CMS es un módulo, incluido `Core` (que es obligatorio y no se puede deshabilitar). Un módulo nuevo:

```
Modules/{Nombre}/
├── module.json                      # slug, name, version, provider, is_core
├── Providers/{Nombre}ServiceProvider.php   # extiende Moon\ModuleKit\AbstractModule
├── Domain/ Application/ Infrastructure/ Http/   # (mismas 4 capas, opcional seguirlas)
├── database/migrations/*.php        # se cargan solas
├── routes/web.php                   # se carga solo
└── resources/
    ├── lang/{es,en}/{grupo}.php      # se carga solo, namespace = slug del modulo
    └── js/Pages/**/*.vue             # Inertia las resuelve solas (ver resources/js/app.js)
```

No hace falta tocar `composer.json` para que autoload funcione: el PSR-4 usa una regla amplia (`"Modules\\": "Modules/"`), así que cualquier clase bajo `Modules/{Nombre}/...` se autocarga por convención.

No se registra el provider en `bootstrap/providers.php`: `ModuleServiceProvider` descubre los módulos no-core y carga los habilitados automáticamente. Después de agregar o actualizar un módulo, ejecuta `php artisan moon:install`.

Un módulo recién detectado queda **habilitado por defecto** (tal como se pidió). Desde `/admin/modules` se puede activar/desactivar cualquiera que no sea `is_core`. Cualquier módulo puede además proteger sus propias rutas con `->middleware('module:slug')` (middleware `Modules\Core\Http\Middleware\EnsureModuleIsEnabled`, reutilizable) para que, si se deshabilita, sus rutas respondan 404 de verdad y no solo desaparezcan del menú.

Las dependencias declaradas en `module.json` se validan antes de sincronizar. No se aceptan módulos ausentes, referencias a sí mismos ni ciclos. La UI bloquea activar un módulo si sus dependencias están deshabilitadas y bloquea deshabilitar uno que todavía tenga dependientes activos.

## Sistema de temas

Un solo tema controla la web pública, autenticación, dashboard y administración. Aeris conserva el diseño actual y es el fallback seguro. Selecciona otro paquete incluido con `APP_THEME=theme-id`; las páginas del tema pueden reemplazar vistas Inertia completas y las demás siguen usando la vista de su módulo con componentes temáticos.

La estructura, los contratos y el procedimiento completo para crear, validar y cambiar un tema están en [docs/themes.md](docs/themes.md).

## Puesta en marcha

El entorno local usa Docker Compose para mantener PHP, Composer, Node, pnpm y los servicios de infraestructura alineados entre colaboradores. En Windows PowerShell, crear primero el archivo local de configuración:

```powershell
Copy-Item .env.example .env
```

En Linux o macOS, usar `cp .env.example .env`. En Linux también se deben ajustar `APP_UID` y `APP_GID` dentro de `.env` con los valores mostrados por `id -u` e `id -g`.

```bash
# Prepara las imágenes e instala dependencias.
docker compose -f docker-compose.dev.yml build php-fpm worker workspace
docker compose -f docker-compose.dev.yml run --rm --no-deps workspace composer install
docker compose -f docker-compose.dev.yml run --rm --no-deps workspace pnpm install --frozen-lockfile
docker compose -f docker-compose.dev.yml run --rm --no-deps workspace php artisan key:generate

# Inicia la infraestructura, instala Core y módulos, y levanta la web.
docker compose -f docker-compose.dev.yml up -d postgres redis mailhog
docker compose -f docker-compose.dev.yml run --rm workspace php artisan moon:install
docker compose -f docker-compose.dev.yml run --rm --no-deps workspace pnpm build
docker compose -f docker-compose.dev.yml up -d

# La cola ya corre como servicio "worker". Para ejecutarla manualmente:
docker compose -f docker-compose.dev.yml run --rm workspace php artisan queue:work redis --queue=provisioning,mail

# Primer administrador (necesario para /admin/realms y /admin/modules):
# 1. Regístrate normalmente en /register
# 2. docker compose -f docker-compose.dev.yml run --rm workspace php artisan moon:make-admin tucorreo@ejemplo.com
```

- App: http://localhost
- Mailhog (correos de verificación/reset en local): http://localhost:8025

Si ya tienes un TrinityCore/AzerothCore real corriendo, crea el reino desde `/admin/realms`. Las bases `auth` y `characters` pueden conectarse directamente o mediante una única puerta SSH por reino. En modo SSH, el panel guarda la llave privada y su passphrase cifradas con `APP_KEY`, verifica el túnel y ambas bases antes de guardar, y nunca vuelve a exponer esos secretos.

OpenSSH valida estrictamente la identidad del servidor remoto. En despliegues con túneles, monta un archivo `known_hosts` de solo lectura en los contenedores web y worker y configura su ubicación con `REALM_SSH_KNOWN_HOSTS_FILE`. La llave usada por el CMS debe ser exclusiva y estar limitada en `authorized_keys` a los destinos MySQL necesarios.

La configuración local está en `docker-compose.dev.yml`. La guía detallada está en [`docs/development/docker.md`](docs/development/docker.md).

## Verificación realizada

La suite automatizada cubre la instalación limpia, migraciones de módulos, dependencias, autenticación, credenciales write-only, páginas públicas y sistema de temas. También se validan la compilación Vite y la sintaxis efectiva de `docker-compose.dev.yml`.

Desarrollo monta el repositorio en `/var/www` y ejecuta los servicios PHP con `APP_UID`/`APP_GID`. Existen Dockerfiles auxiliares para construir imágenes de staging y producción, pero el repositorio no incluye archivos Compose de despliegue para esos entornos.

Continúa pendiente la validación contra un `worldserver` real.

## Qué falta / limitaciones conocidas

Fuera de alcance a propósito en esta entrega (según lo acordado):
- Módulos de contenido adicionales a News (changelog, votos, tienda, etc.).
- Dashboard variando según `gmlevel`.
- Sistema de roles/permisos granular: hoy solo existe una bandera `is_admin` en `users`, suficiente para proteger `/admin/*`. El primer admin se otorga por Artisan (`moon:make-admin`), no hay UI para auto-promoverse (a propósito).
- La cobertura automatizada todavía es inicial y debe crecer junto con cada módulo y comportamiento nuevo.
- i18n: hay `es`/`en` para el módulo Core; un selector de idioma en la UI queda para cuando haya más de un módulo con contenido traducible.
