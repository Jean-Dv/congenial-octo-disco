# Entorno Docker de desarrollo

## Arquitectura

El repositorio incluye un único archivo Compose: `docker-compose.dev.yml`.
Todos los comandos deben indicar ese archivo porque su nombre no es el
predeterminado de Docker Compose.

| Servicio | Responsabilidad |
| --- | --- |
| `web` | Nginx y puerto HTTP `80` |
| `php-fpm` | Aplicación Laravel |
| `worker` | Colas `provisioning` y `mail` |
| `workspace` | Composer, Artisan, Node y pnpm |
| `postgres` | Base de datos del CMS |
| `redis` | Caché, sesiones y colas |
| `mailhog` | Correo local, interfaz en el puerto `8025` |

El código del repositorio se monta en `/var/www` para reflejar los cambios
locales. En Linux, `APP_UID` y `APP_GID` de `.env` deben coincidir con
`id -u` e `id -g`; Docker Desktop puede conservar los valores predeterminados.

## Primera instalación

Desde la raíz del repositorio:

```bash
cp .env.example .env
docker compose -f docker-compose.dev.yml build php-fpm worker workspace
docker compose -f docker-compose.dev.yml run --rm --no-deps workspace composer install
docker compose -f docker-compose.dev.yml run --rm --no-deps workspace pnpm install --frozen-lockfile
docker compose -f docker-compose.dev.yml run --rm --no-deps workspace php artisan key:generate
docker compose -f docker-compose.dev.yml up -d postgres redis mailhog
docker compose -f docker-compose.dev.yml run --rm workspace php artisan moon:install
docker compose -f docker-compose.dev.yml run --rm --no-deps workspace pnpm build
docker compose -f docker-compose.dev.yml up -d
```

`moon:install` es idempotente: migra Core, sincroniza y valida los módulos,
registra sus providers en orden de dependencias y después ejecuta las
migraciones de los módulos habilitados.

- Aplicación: `http://localhost`
- Health check: `http://localhost/up`
- Mailhog: `http://localhost:8025`

Para crear el primer administrador:

1. Registra una cuenta en `http://localhost/register`.
2. Ejecuta:

```bash
docker compose -f docker-compose.dev.yml run --rm workspace php artisan moon:make-admin tucorreo@ejemplo.com
```

## Operación diaria

```bash
docker compose -f docker-compose.dev.yml up -d
docker compose -f docker-compose.dev.yml ps
docker compose -f docker-compose.dev.yml logs -f web php-fpm worker
docker compose -f docker-compose.dev.yml down
```

Después de descargar cambios que agreguen migraciones o módulos:

```bash
docker compose -f docker-compose.dev.yml run --rm workspace php artisan moon:install
docker compose -f docker-compose.dev.yml run --rm --no-deps workspace composer install
docker compose -f docker-compose.dev.yml run --rm --no-deps workspace pnpm install --frozen-lockfile
docker compose -f docker-compose.dev.yml run --rm --no-deps workspace pnpm build
```

`docker compose down` conserva los volúmenes. `--volumes` elimina los datos
locales de PostgreSQL y Redis, por lo que solo debe usarse en instalaciones
desechables.

## Verificación

```powershell
powershell -ExecutionPolicy Bypass -File tests/Infrastructure/DockerConfiguration.Tests.ps1
```

```bash
docker compose -f docker-compose.dev.yml config --quiet
docker compose -f docker-compose.dev.yml run --rm --no-deps workspace php artisan test
docker compose -f docker-compose.dev.yml run --rm --no-deps workspace pnpm test
docker compose -f docker-compose.dev.yml run --rm --no-deps workspace pnpm build
```

PHPUnit fuerza SQLite en memoria, por lo que la suite no modifica PostgreSQL.
Los Dockerfiles de staging y producción son recursos de imagen; este
repositorio todavía no publica archivos Compose para desplegar esos entornos.

## Solución de problemas

- Si falta `pdo_pgsql`, reconstruye `php-fpm`, `worker` y `workspace`.
- Si `pnpm` no existe, reconstruye `workspace`.
- Si la cola termina, revisa `docker compose -f docker-compose.dev.yml logs worker`.
- Si falta `APP_KEY`, vuelve a ejecutar `php artisan key:generate` desde `workspace`.
- Si hay errores de permisos en Linux, corrige `APP_UID`/`APP_GID` y reconstruye los servicios PHP.
- Si el puerto 80 está ocupado, cambia el mapeo de `web` en tu copia local de Compose.
