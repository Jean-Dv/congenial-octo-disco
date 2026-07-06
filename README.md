# Moon CMS — Core

Núcleo hexagonal para un CMS de servidores privados de World of Warcraft, construido con Laravel 12 + Inertia/Vue. Esta entrega incluye **solo el core**: identidad (registro/login), configuración de reinos multi-core, aprovisionamiento automático de cuentas de juego, comunicación remota con el core (SOAP), un dashboard placeholder y el sistema de módulos sobre el que se construirá todo lo demás.

No incluye módulos de contenido (News, Changelog, Vote, etc.) — eso es intencional, según el alcance acordado.

## Índice

- [Arquitectura](#arquitectura)
- [Identidad: por qué dos "usuarios" separados](#identidad-por-qué-dos-usuarios-separados)
- [Multi-realm / multi-core](#multi-realm--multi-core)
- [Contraseñas de la cuenta de juego (SRP6)](#contraseñas-de-la-cuenta-de-juego-srp6)
- [Comunicación remota (SOAP hoy, lo que sea mañana)](#comunicación-remota-soap-hoy-lo-que-sea-mañana)
- [Aprovisionamiento asíncrono y seguridad de las colas](#aprovisionamiento-asíncrono-y-seguridad-de-las-colas)
- [Sistema de módulos](#sistema-de-módulos)
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

Las credenciales de conexión de cada reino (contraseñas de MySQL y de SOAP) se guardan cifradas en Postgres (`RealmModel` usa el cast `encrypted:array` de Laravel sobre `auth_database`, `characters_database` y `remote_console`).

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

Solo falta:
1. Registrar el provider en `bootstrap/providers.php` (o, si prefieres carga 100% dinámica sin tocar ese archivo, replicar cómo `App\Providers\ModuleServiceProvider` ya registra automáticamente cualquier módulo no-core que esté `enabled` en la tabla `modules` — Core está forzado siempre por ser `is_core`).
2. Ejecutar `php artisan migrate` para sus migraciones.

Un módulo recién detectado queda **habilitado por defecto** (tal como se pidió). Desde `/admin/modules` se puede activar/desactivar cualquiera que no sea `is_core`. Cualquier módulo puede además proteger sus propias rutas con `->middleware('module:slug')` (middleware `Modules\Core\Http\Middleware\EnsureModuleIsEnabled`, reutilizable) para que, si se deshabilita, sus rutas respondan 404 de verdad y no solo desaparezcan del menú.

## Puesta en marcha

Este entorno de generación no tiene acceso a Packagist ni a un runtime de PHP persistente, así que **no se pudo ejecutar `composer install` / `npm install` aquí**. Pasos para levantarlo en tu máquina:

```bash
cp .env.example .env

docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app npm install
docker compose exec app npm run build   # o "npm run dev" en otra terminal para desarrollo

# Cola de aprovisionamiento (ya corre como servicio "queue" en docker-compose,
# pero si la ejecutas manualmente):
docker compose exec app php artisan queue:work redis --queue=provisioning,mail

# Primer administrador (necesario para /admin/realms y /admin/modules):
# 1. Regístrate normalmente en /register
# 2. docker compose exec app php artisan moon:make-admin tucorreo@ejemplo.com
```

- App: http://localhost:8080
- Mailhog (correos de verificación/reset en local): http://localhost:8025
- MySQL de prueba con esquema mínimo tipo TrinityCore (opcional, sin depender de un core real): `docker compose --profile mock-realm up -d`

Si ya tienes un TrinityCore/AzerothCore real corriendo, crea el reino desde `/admin/realms` apuntando su BD `auth` y su SOAP directamente (no hace falta el `mock-realm`).

## Verificación realizada

Sin PHP/Composer con red disponible en este entorno, se verificó lo que sí se pudo:

- **Sintaxis**: los 132 archivos `.php` del proyecto pasan `php -l` sin errores (se instaló PHP 8.3 + ext-gmp + ext-soap vía apt para esto).
- **Coherencia de namespaces**: los 223 `use ...;` internos del proyecto resuelven a un archivo real bajo el mapeo PSR-4 (sin typos de importación).
- **Capa de dominio 100% desacoplada**: las 36 clases/interfaces/enums de `Domain/` y del shared kernel `Moon\` cargan y son mutuamente consistentes (interfaces completamente implementadas, sin depender de Laravel en absoluto) usando un autoloader mínimo aislado. La única excepción esperada es `AbstractModule` (depende de `Illuminate\Support\ServiceProvider` a propósito, es el punto de integración con Laravel).
- **SRP6 cross-validado contra una implementación de referencia independiente** (paquete npm `trinitycore-srp6`): mismo username/password/salt fijos → verifier byte-a-byte idéntico entre la implementación PHP de este proyecto y la librería JS. También se probó el ciclo completo generar→verificar (contraseña correcta acepta, incorrecta rechaza).

Lo que **no** se pudo verificar por falta de entorno: arranque real de Laravel, migraciones contra Postgres real, ida y vuelta HTTP/Inertia, ni una conexión SOAP/MySQL real contra un worldserver. Recomendado antes de producción: correr el flujo de registro completo contra tu TrinityCore real y confirmar que el personaje puede loguear en el cliente.

## Qué falta / limitaciones conocidas

Fuera de alcance a propósito en esta entrega (según lo acordado):
- Cualquier módulo de contenido (noticias, changelog, votos, tienda, etc.).
- Dashboard variando según `gmlevel`.
- Sistema de roles/permisos granular: hoy solo existe una bandera `is_admin` en `users`, suficiente para proteger `/admin/*`. El primer admin se otorga por Artisan (`moon:make-admin`), no hay UI para auto-promoverse (a propósito).
- Tests automatizados: no se escribieron todavía (se acordó explícitamente dejarlo para después), pero la capa de dominio quedó deliberadamente aislada de Laravel para que sea trivial testear con Pest cuando se retome — sin mocks de framework, solo los puertos.
- i18n: hay `es`/`en` para el módulo Core; un selector de idioma en la UI queda para cuando haya más de un módulo con contenido traducible.
