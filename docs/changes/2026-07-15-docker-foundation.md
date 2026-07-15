# Reparación inicial del entorno Docker

Fecha: 2026-07-15  
Rama: `fix/docker-foundation`

## Objetivo

Completar el entorno local que estaba descrito en el README pero no representado en Docker Compose, y establecer reglas de colaboración que impidan integrar cambios sin documentación, pruebas y aprobación de Jean.

## Cambios

- Se agregaron los servicios `app`, `nginx` y `queue` al entorno normal.
- Se agregó `mock-realm` como perfil opcional para pruebas con un esquema MySQL mínimo.
- Se incorporaron healthchecks y dependencias de arranque para PHP, PostgreSQL, Redis y Nginx.
- Se fijaron las imágenes principales y las versiones de Node y pnpm.
- Se corrigió el origen de instalación de NodeSource.
- Se agregó `.dockerignore` para excluir secretos, dependencias y artefactos locales.
- Se documentaron primera instalación, operación diaria, verificación, solución de problemas y reversión.
- Se agregó una prueba estática de la configuración Docker.
- Se limitó la autorización de scripts de dependencias JavaScript a `esbuild`, requerido por Vite.

## Decisiones

- La instalación de Composer y pnpm se realiza antes del primer `docker compose up -d` completo. Esto evita que el worker de colas arranque sin `vendor/autoload.php`.
- El código se monta como volumen para conservar un ciclo de desarrollo rápido.
- Los datos de PostgreSQL, Redis y el reino simulado usan volúmenes separados.
- El reino simulado no se inicia por defecto para no consumir recursos cuando se trabaja solo en el CMS.

## Verificaciones ejecutadas

| Verificación | Resultado |
| --- | --- |
| `tests/Infrastructure/DockerConfiguration.Tests.ps1` | Aprobada |
| `git diff --check` | Aprobada |
| `pnpm install --frozen-lockfile` | Aprobada; lockfile verificado y `esbuild` instalado |
| `pnpm build` | Parcial: Vite transformó 30 módulos y se detuvo porque falta `vendor/tightenco/ziggy` |
| `docker compose config --quiet` | Pendiente: Docker no está instalado en el equipo de ejecución |
| `docker compose build app` | Pendiente: Docker no está instalado en el equipo de ejecución |
| Pruebas PHP | Pendientes: PHP y Composer no están instalados fuera de Docker |

La ausencia de Ziggy no representa un cambio en las dependencias del proyecto: `ziggy-js` está mapeado deliberadamente al paquete Composer `vendor/tightenco/ziggy`. El flujo completo debe verificarse en una máquina con Docker ejecutando primero `composer install`, como indica la guía.

## Condición antes de aprobación

Antes de que Jean apruebe el pull request deben ejecutarse en una máquina con Docker:

```bash
docker compose config --quiet
docker compose build app
docker compose run --rm --no-deps app composer install
docker compose run --rm --no-deps app pnpm install --frozen-lockfile
docker compose up -d postgres redis mailhog
docker compose run --rm app php artisan migrate
docker compose run --rm --no-deps app pnpm build
docker compose up -d
docker compose ps
docker compose exec app php artisan test
```

El PR no debe fusionarse si alguno de esos comandos falla sin una explicación y aprobación explícita del riesgo.

## Reversión

Revertir el commit de esta rama restaura la configuración anterior. No se deben eliminar volúmenes durante la reversión; `docker compose down` conserva los datos locales.
