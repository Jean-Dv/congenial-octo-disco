$ErrorActionPreference = 'Stop'
Set-StrictMode -Version Latest

# Keep this verification runnable even when Docker is not installed.
$repositoryRoot = Resolve-Path (Join-Path $PSScriptRoot '..\..')
$composePath = Join-Path $repositoryRoot 'docker-compose.yml'
$dockerfilePath = Join-Path $repositoryRoot 'docker\php\Dockerfile'
$nginxPath = Join-Path $repositoryRoot 'docker\nginx\default.conf'
$documentationPath = Join-Path $repositoryRoot 'docs\development\docker.md'
$dockerignorePath = Join-Path $repositoryRoot '.dockerignore'

$compose = Get-Content -LiteralPath $composePath -Raw
$dockerfile = Get-Content -LiteralPath $dockerfilePath -Raw
$nginx = Get-Content -LiteralPath $nginxPath -Raw

foreach ($service in @('app', 'nginx', 'queue', 'postgres', 'redis', 'mailhog', 'mock-realm')) {
    if ($compose -notmatch "(?m)^    $([regex]::Escape($service)):") {
        throw "Falta el servicio '$service' en docker-compose.yml."
    }
}

if ($dockerfile -notmatch 'https://deb\.nodesource\.com/setup_\$\{NODE_MAJOR\}\.x') {
    throw 'El Dockerfile no usa el instalador versionado de NodeSource.'
}

if ($dockerfile -match 'https://nodesource\.com\s*\|') {
    throw 'El Dockerfile reintrodujo la URL no ejecutable de NodeSource.'
}

if ($dockerfile -notmatch 'corepack prepare "pnpm@\$\{PNPM_VERSION\}" --activate') {
    throw 'La version de pnpm no esta fijada mediante Corepack.'
}

if ($nginx -notmatch 'fastcgi_pass\s+app:9000') {
    throw 'Nginx no esta conectado al servicio PHP app:9000.'
}

if (-not (Test-Path -LiteralPath $documentationPath)) {
    throw 'Falta la documentacion del entorno Docker.'
}

if (-not (Test-Path -LiteralPath $dockerignorePath)) {
    throw 'Falta .dockerignore para proteger el contexto de construccion.'
}

$dockerignore = Get-Content -LiteralPath $dockerignorePath -Raw
foreach ($protectedPath in @('.git', '.env', 'node_modules', 'vendor')) {
    if ($dockerignore -notmatch "(?m)^$([regex]::Escape($protectedPath))$") {
        throw "Falta excluir '$protectedPath' del contexto Docker."
    }
}

$docker = Get-Command docker -ErrorAction SilentlyContinue
if ($null -ne $docker) {
    & $docker.Source compose --file $composePath config --quiet
    if ($LASTEXITCODE -ne 0) {
        throw 'docker compose config detecto una configuracion invalida.'
    }
}

Write-Host 'DockerConfiguration.Tests.ps1: OK'
