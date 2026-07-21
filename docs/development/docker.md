# Entornos Docker

## Objetivo

Moon CMS usa una misma base de imagen para desarrollo, staging y producción. La diferencia entre entornos se limita a los archivos Compose y a los stages seleccionados; las versiones y extensiones de PHP permanecen alineadas.

Docker es el único requisito de ejecución local. PHP, Composer, Node, pnpm, PostgreSQL y Redis no se instalan directamente en el equipo.

## Arquitectura

| Archivo o stage | Responsabilidad |
| --- | --- |
| `docker-compose.yml` | Servicios `app`, `nginx` y `queue` compartidos |
| `docker-compose.override.yml` | Desarrollo local, bind mount, UID/GID, workspace y servicios locales |
| `docker-compose.staging.yml` | Imagen inmutable de staging |
| `docker-compose.production.yml` | Imagen inmutable de producción |
| `php-base` | PHP 8.3 y extensiones comunes |
| `development` | Composer, Node, pnpm y usuario del desarrollador |
| `production` / `staging` | Código, dependencias y assets incluidos en la imagen |
| `nginx` | Configuración web y archivos públicos compilados |

`docker compose` carga automáticamente el archivo base y el override de desarrollo. Staging y producción se ejecutan indicando sus dos archivos con `-f`, por lo que nunca heredan el bind mount local.

## Permisos de desarrollo

El repositorio se monta para reflejar cambios inmediatamente, pero las rutas con escrituras frecuentes se guardan en volúmenes Docker:

- `vendor`
- `node_modules`
- almacén de pnpm
- assets compilados en `public/build`
- `bootstrap/cache`
- `storage/framework`
- `storage/logs`

El entrypoint prepara esas rutas, repara propietarios incorrectos en los archivos de runtime y abandona privilegios antes de ejecutar Composer, Artisan, pnpm o la cola. PHP-FPM conserva su proceso maestro estándar y crea workers con el usuario `www-data`; el código de las peticiones no se ejecuta como root.

El servicio `workspace` es la consola de mantenimiento. Los comandos manuales se ejecutan con `docker compose run --rm workspace ...` para no entrar como root al contenedor de PHP-FPM. No usar `docker compose exec app` para Composer, Artisan o pnpm; `docker exec` omite el entrypoint y puede crear cachés o logs que la cola no pueda modificar.

En Linux, configurar en `.env`:

```dotenv
APP_UID=1000
APP_GID=1000
```

Reemplazar los valores con el resultado de `id -u` e `id -g`. Docker Desktop para Windows y macOS puede conservar los valores predeterminados.

## Primera instalación

Todos los comandos se ejecutan desde la raíz del repositorio.

### 1. Crear la configuración local

Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

Linux o macOS:

```bash
cp .env.example .env
```

`.env` contiene configuración local y nunca se sube a Git.

### 2. Construir la imagen de desarrollo

```bash
docker compose build app
```

Este comando construye el stage `development` porque Docker Compose aplica automáticamente `docker-compose.override.yml`.

### 3. Instalar dependencias y generar la clave

```bash
docker compose run --rm --no-deps workspace composer install
docker compose run --rm --no-deps workspace pnpm install --frozen-lockfile
docker compose run --rm --no-deps workspace php artisan key:generate
```

Composer y pnpm escriben en volúmenes Docker. `key:generate` guarda una clave única en el `.env` local.

### 4. Preparar la infraestructura y la aplicación

```bash
docker compose up -d postgres redis mailhog
docker compose run --rm workspace php artisan migrate
docker compose run --rm workspace php artisan moon:sync-modules
docker compose run --rm --no-deps workspace pnpm build
```

Las migraciones preparan PostgreSQL. `moon:sync-modules` registra los módulos encontrados y Vite genera los assets de la interfaz.

### 5. Iniciar y comprobar servicios

```bash
docker compose up -d
docker compose ps
```

- Aplicación: `http://localhost:8080`
- Health check: `http://localhost:8080/up`
- Mailhog: `http://localhost:8025`

Todos los servicios visibles en `docker compose ps` deben estar activos; `app`, PostgreSQL y Redis deben mostrar estado saludable. El healthcheck de `app` consulta el endpoint FastCGI `/ping`, por lo que valida que PHP-FPM esté respondiendo y no solamente que su archivo de configuración sea válido.

## Primer administrador

1. Registrar una cuenta en `http://localhost:8080/register`.
2. Ejecutar:

```bash
docker compose run --rm workspace php artisan moon:make-admin tucorreo@ejemplo.com
```

Después de iniciar sesión, las rutas `/admin/realms` y `/admin/modules` estarán disponibles.

## Reino local de prueba

El mock incluye solamente las tablas de autenticación necesarias para comprobar el aprovisionamiento de cuentas; no ejecuta un `worldserver`.

```bash
docker compose --profile mock-realm up -d mock-realm
```

Desde los contenedores, la conexión es `mock-realm:3306`, base `auth`, usuario `trinity` y contraseña `trinity`. Desde el host se publica en `127.0.0.1:3307`.

## Operación diaria

```bash
docker compose up -d
docker compose logs -f app nginx queue
docker compose down
```

`docker compose down` conserva los volúmenes. No agregar `--volumes` si existen datos locales que deban preservarse.

Después de actualizar dependencias:

```bash
docker compose run --rm --no-deps workspace composer install
docker compose run --rm --no-deps workspace pnpm install --frozen-lockfile
docker compose run --rm --no-deps workspace pnpm build
```

## Staging y producción

Estos entornos no montan el código del host. Composer y Vite se ejecutan durante el build y los resultados quedan incluidos en imágenes inmutables.

Staging:

```bash
docker compose -f docker-compose.yml -f docker-compose.staging.yml config --quiet
docker compose -f docker-compose.yml -f docker-compose.staging.yml build
```

Producción:

```bash
docker compose -f docker-compose.yml -f docker-compose.production.yml config --quiet
docker compose -f docker-compose.yml -f docker-compose.production.yml build
```

PostgreSQL, Redis, correo, secretos y la clave de Laravel deben inyectarse mediante el sistema de despliegue. Los servicios locales del override no forman parte de estos entornos.

## QA antes de un pull request

```powershell
powershell -ExecutionPolicy Bypass -File tests/Infrastructure/DockerConfiguration.Tests.ps1
```

```bash
docker compose config --quiet
docker compose build app
docker compose run --rm --no-deps workspace php artisan test
docker compose run --rm --no-deps workspace pnpm build
```

Además, comprobar registro, correo, login, dashboard, cierre de sesión y recuperación de contraseña desde el navegador. Un cambio de infraestructura no está listo para pull request mientras alguna de estas pruebas permanezca sin resultado.

PHPUnit fuerza SQLite en memoria incluso cuando Docker inyecta PostgreSQL en el contenedor. La suite no debe migrar, truncar ni modificar la base local del CMS.

## Prueba de instalación limpia

La validación más confiable se realiza en un clon nuevo y siguiendo únicamente esta guía. Si se reutiliza una instalación local desechable, eliminar volúmenes también elimina PostgreSQL, Redis, dependencias y cuentas de prueba:

```bash
docker compose down --volumes --remove-orphans
```

No ejecutar esta orden sobre datos que deban conservarse.

## Solución de problemas

- Si `queue` termina, revisar primero `docker compose logs queue`, repetir `composer install` y comprobar el volumen `moon-vendor`.
- Si aparece `Permission denied` en `storage/logs`, recrear `app` y `queue` para que el entrypoint repare el volumen: `docker compose up -d --force-recreate app queue`.
- Si Linux crea archivos con otro propietario, corregir `APP_UID` y `APP_GID`, reconstruir `app` y recrear solamente los volúmenes locales afectados.
- Si falta la clave de cifrado, ejecutar `docker compose run --rm --no-deps workspace php artisan key:generate`.
- Si un puerto está ocupado, cambiar las variables `FORWARD_*` en `.env`.
- Si Composer exige una versión superior de PHP, revisar `config.platform.php` y `composer.lock`; no ejecutar `composer update` sin auditar el diff.
- Si cambia el Dockerfile, ejecutar `docker compose build app`. Reservar `--no-cache` para diagnosticar problemas de caché.

## Reversión

Los cambios se revierten restaurando los archivos Compose, `docker/php/Dockerfile`, el entrypoint y la documentación desde Git, y reconstruyendo las imágenes. Los volúmenes se conservan salvo que su eliminación se apruebe explícitamente.
