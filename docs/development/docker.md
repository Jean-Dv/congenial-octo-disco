# Entorno local con Docker

## Objetivo

El entorno Docker permite ejecutar Moon CMS sin instalar PHP, Composer, Node, PostgreSQL ni Redis directamente en el equipo. La configuración está orientada al desarrollo local y monta el repositorio dentro de los contenedores para reflejar los cambios de código inmediatamente.

El archivo `.dockerignore` excluye dependencias, secretos, artefactos compilados y metadatos locales del contexto de construcción.

La versión de pnpm está fijada en `package.json` y en la imagen. La política de scripts de dependencias autoriza únicamente la compilación de `esbuild`, requerida por Vite; cualquier binario adicional debe revisarse y aprobarse explícitamente antes de agregarlo a `pnpm-workspace.yaml`.

## Servicios

| Servicio | Responsabilidad | Puerto local predeterminado |
| --- | --- | --- |
| `app` | PHP 8.3 FPM, Composer, Node 22 y pnpm 9 | Interno |
| `nginx` | Entrada HTTP de la aplicación | `8080` |
| `queue` | Jobs de aprovisionamiento y correo | Interno |
| `postgres` | Base de datos propia del CMS | `5432` |
| `redis` | Colas, sesiones y caché | `6379` |
| `mailhog` | SMTP y visor de correo local | `1025` / `8025` |
| `mock-realm` | Esquema MySQL mínimo compatible con TrinityCore | `3307` |

`mock-realm` pertenece al perfil opcional del mismo nombre y no se inicia durante el arranque normal.

## Primera instalación

1. Copiar `.env.example` como `.env`.
2. Construir la imagen de la aplicación:

   ```bash
   docker compose build app
   ```

3. Instalar dependencias antes de iniciar la cola:

   ```bash
   docker compose run --rm --no-deps app composer install
   docker compose run --rm --no-deps app pnpm install --frozen-lockfile
   docker compose run --rm --no-deps app php artisan key:generate
   ```

4. Iniciar dependencias y preparar la base de datos:

   ```bash
   docker compose up -d postgres redis mailhog
   docker compose run --rm app php artisan migrate
   docker compose run --rm --no-deps app pnpm build
   ```

5. Iniciar la aplicación completa:

   ```bash
   docker compose up -d
   docker compose ps
   ```

La aplicación debe responder en `http://localhost:8080` y Mailhog en `http://localhost:8025`.

## Operación diaria

```bash
docker compose up -d
docker compose logs -f app nginx queue
docker compose down
```

Después de actualizar dependencias PHP o JavaScript:

```bash
docker compose run --rm --no-deps app composer install
docker compose run --rm --no-deps app pnpm install --frozen-lockfile
docker compose run --rm --no-deps app pnpm build
```

## Reino de prueba opcional

```bash
docker compose --profile mock-realm up -d mock-realm
```

La conexión desde el host usa `127.0.0.1:3307`. Desde otro contenedor de la red `moon` se usa `mock-realm:3306`.

## Verificación

La comprobación estática funciona incluso en un equipo sin Docker:

```powershell
powershell -ExecutionPolicy Bypass -File tests/Infrastructure/DockerConfiguration.Tests.ps1
```

Cuando Docker está instalado, la misma prueba ejecuta adicionalmente `docker compose config --quiet`. Antes de aprobar cambios de infraestructura también se recomienda:

```bash
docker compose build app
docker compose up -d
docker compose ps
docker compose exec app php artisan test
docker compose exec app pnpm build
```

## Solución de problemas

- Si `queue` termina inmediatamente, confirmar que existe `vendor/autoload.php` y repetir `composer install` dentro de `app`.
- Si Laravel indica que falta la clave de cifrado, ejecutar `docker compose run --rm --no-deps app php artisan key:generate`.
- Si un puerto está ocupado, configurar `FORWARD_APP_PORT`, `FORWARD_DB_PORT`, `FORWARD_REDIS_PORT`, `FORWARD_MAILHOG_PORT`, `FORWARD_SMTP_PORT` o `FORWARD_MOCK_REALM_PORT` en `.env`.
- Si cambió el Dockerfile, reconstruir con `docker compose build --no-cache app` solo cuando sea necesario.

## Reversión

Los cambios de infraestructura se revierten restaurando `docker-compose.yml` y `docker/php/Dockerfile` desde Git y reconstruyendo la imagen. `docker compose down` conserva los volúmenes de datos. No usar `docker compose down -v` salvo que se haya aprobado explícitamente eliminar las bases locales.
