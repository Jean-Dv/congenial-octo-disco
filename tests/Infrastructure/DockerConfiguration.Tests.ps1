$ErrorActionPreference = 'Stop'
Set-StrictMode -Version Latest

# Keep this verification runnable even when Docker is not installed.
$repositoryRoot = Resolve-Path (Join-Path $PSScriptRoot '..\..')
$composePath = Join-Path $repositoryRoot 'docker-compose.yml'
$developmentComposePath = Join-Path $repositoryRoot 'docker-compose.override.yml'
$stagingComposePath = Join-Path $repositoryRoot 'docker-compose.staging.yml'
$productionComposePath = Join-Path $repositoryRoot 'docker-compose.production.yml'
$dockerfilePath = Join-Path $repositoryRoot 'docker\php\Dockerfile'
$entrypointPath = Join-Path $repositoryRoot 'docker\php\entrypoint.sh'
$composerPath = Join-Path $repositoryRoot 'composer.json'
$packagePath = Join-Path $repositoryRoot 'package.json'
$phpunitPath = Join-Path $repositoryRoot 'phpunit.xml'
$nginxPath = Join-Path $repositoryRoot 'docker\nginx\default.conf'
$documentationPath = Join-Path $repositoryRoot 'docs\development\docker.md'
$dockerignorePath = Join-Path $repositoryRoot '.dockerignore'

$compose = Get-Content -LiteralPath $composePath -Raw
$developmentCompose = Get-Content -LiteralPath $developmentComposePath -Raw
$stagingCompose = Get-Content -LiteralPath $stagingComposePath -Raw
$productionCompose = Get-Content -LiteralPath $productionComposePath -Raw
$dockerfile = Get-Content -LiteralPath $dockerfilePath -Raw
$entrypoint = Get-Content -LiteralPath $entrypointPath -Raw
$composer = Get-Content -LiteralPath $composerPath -Raw | ConvertFrom-Json
$package = Get-Content -LiteralPath $packagePath -Raw | ConvertFrom-Json
$phpunit = Get-Content -LiteralPath $phpunitPath -Raw
$nginx = Get-Content -LiteralPath $nginxPath -Raw
$documentation = Get-Content -LiteralPath $documentationPath -Raw

foreach ($service in @('app', 'nginx', 'queue')) {
    if ($compose -notmatch "(?m)^    $([regex]::Escape($service)):") {
        throw "Falta el servicio compartido '$service' en docker-compose.yml."
    }
}

foreach ($service in @('postgres', 'redis', 'mailhog', 'mock-realm', 'workspace')) {
    if ($developmentCompose -notmatch "(?m)^    $([regex]::Escape($service)):") {
        throw "Falta el servicio local '$service' en docker-compose.override.yml."
    }
}

foreach ($stage in @('php-base', 'development', 'production', 'staging', 'nginx')) {
    if ($dockerfile -notmatch "(?m)^FROM .+ AS $([regex]::Escape($stage))$") {
        throw "Falta el stage Docker '$stage'."
    }
}

if ($dockerfile -notmatch 'https://deb\.nodesource\.com/setup_\$\{NODE_MAJOR\}\.x') {
    throw 'El stage de desarrollo no usa el instalador versionado de NodeSource.'
}

if ($dockerfile -notmatch 'corepack prepare "pnpm@\$\{PNPM_VERSION\}" --activate') {
    throw 'La version de pnpm no esta fijada mediante Corepack.'
}

if ($dockerfile -notmatch '(?m)^ARG PNPM_VERSION=(?<version>[^\r\n]+)$') {
    throw 'El Dockerfile no declara PNPM_VERSION.'
}

if ($package.packageManager -ne "pnpm@$($Matches.version)") {
    throw 'package.json y el Dockerfile deben usar la misma version de pnpm.'
}

if ($dockerfile -notmatch '(?m)^FROM php:8\.3-') {
    throw 'La imagen base debe usar PHP 8.3.'
}

if ($entrypoint -notmatch 'if \[ "\$\{1:-\}" = ''php-fpm'' \]') {
    throw 'El entrypoint debe permitir que PHP-FPM administre sus workers.'
}

if ($entrypoint -notmatch 'exec gosu www-data') {
    throw 'El entrypoint de desarrollo debe abandonar privilegios antes de ejecutar procesos.'
}

if ($entrypoint -notmatch 'repair_owner /var/www/html/storage/logs') {
    throw 'El entrypoint debe reparar archivos de runtime creados con propietarios incorrectos.'
}

if ($entrypoint -notmatch 'repair_owner /var/www/html/vendor' -or $developmentCompose -notmatch 'MOON_PREPARE_DEV_VOLUMES:\s+"0"') {
    throw 'Los volúmenes de dependencias deben repararse sin cargar ese escaneo en la cola.'
}

if ($developmentCompose -notmatch '(?ms)^    workspace:.*?profiles:\s*\["tools"\].*?command:\s*\["sleep", "infinity"\].*?networks:\s*- moon') {
    throw 'Desarrollo debe ofrecer un workspace aislado para ejecutar comandos sin usar el proceso PHP-FPM.'
}

if ($documentation -match 'docker compose exec app (php artisan|composer|pnpm)') {
    throw 'La documentacion no debe ejecutar herramientas como root dentro del servicio app.'
}

if ($compose -notmatch 'cgi-fcgi -bind -connect 127\.0\.0\.1:9000' -or $dockerfile -notmatch 'ping\.path = /ping') {
    throw 'El healthcheck debe consultar un proceso PHP-FPM activo mediante FastCGI.'
}

if ($dockerfile -notmatch 'ENV MOON_PREPARE_DEV_VOLUMES=1' -or $entrypoint -notmatch 'MOON_PREPARE_DEV_VOLUMES') {
    throw 'La preparacion de volumenes de dependencias debe limitarse a desarrollo.'
}

foreach ($volumePath in @('/var/www/html/vendor', '/var/www/html/node_modules', '/var/www/html/public/build', '/var/www/html/storage/framework', '/var/www/html/storage/logs')) {
    if ($developmentCompose -notmatch [regex]::Escape($volumePath)) {
        throw "Desarrollo debe aislar '$volumePath' en un volumen Docker."
    }
}

if ($developmentCompose -notmatch 'APP_UID:\s+\$\{APP_UID:-1000\}' -or $developmentCompose -notmatch 'APP_GID:\s+\$\{APP_GID:-1000\}') {
    throw 'Desarrollo debe mapear APP_UID y APP_GID para evitar archivos con propietario root.'
}

if ($stagingCompose -notmatch '(?m)^\s+target: staging$') {
    throw 'La configuracion de staging debe seleccionar su stage explicito.'
}

if ($productionCompose -notmatch '(?m)^\s+target: production$') {
    throw 'La configuracion de produccion debe seleccionar su stage explicito.'
}

if ($stagingCompose -match '\.:/var/www/html' -or $productionCompose -match '\.:/var/www/html') {
    throw 'Staging y produccion no deben montar el codigo fuente del host.'
}

if ($composer.config.platform.php -ne '8.3.0') {
    throw 'Composer debe resolver dependencias para la plataforma minima PHP 8.3.0.'
}

if ($phpunit -notmatch '<server name="DB_CONNECTION" value="sqlite" force="true"/>') {
    throw 'PHPUnit debe forzar SQLite incluso cuando Docker inyecta PostgreSQL.'
}

if ($nginx -notmatch 'fastcgi_pass\s+app:9000') {
    throw 'Nginx no esta conectado al servicio PHP app:9000.'
}

foreach ($requiredPath in @($documentationPath, $dockerignorePath, $entrypointPath, $phpunitPath, $developmentComposePath, $stagingComposePath, $productionComposePath)) {
    if (-not (Test-Path -LiteralPath $requiredPath)) {
        throw "Falta el archivo requerido '$requiredPath'."
    }
}

$dockerignore = Get-Content -LiteralPath $dockerignorePath -Raw
foreach ($protectedPath in @('.git', '.env', 'node_modules', '.pnpm-store', 'vendor')) {
    if ($dockerignore -notmatch "(?m)^$([regex]::Escape($protectedPath))$") {
        throw "Falta excluir '$protectedPath' del contexto Docker."
    }
}

$docker = Get-Command docker -ErrorAction SilentlyContinue
if ($null -ne $docker) {
    $composeVariants = @(
        @($composePath, $developmentComposePath),
        @($composePath, $stagingComposePath),
        @($composePath, $productionComposePath)
    )

    foreach ($variant in $composeVariants) {
        & $docker.Source compose --file $variant[0] --file $variant[1] config --quiet
        if ($LASTEXITCODE -ne 0) {
            throw "docker compose config fallo para '$($variant[1])'."
        }
    }
}

Write-Host 'DockerConfiguration.Tests.ps1: OK'
