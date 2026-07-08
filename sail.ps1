param(
    [Parameter(Position = 0)]
    [ValidateSet('build', 'up', 'down', 'setup', 'migrate', 'test', 'pint', 'redis', 'logs', 'sh', 'shell')]
    [string]$Action = 'up'
)

$ErrorActionPreference = 'Stop'
$AppService = 'laravel.test'

function Resolve-DockerCli {
    $cmd = Get-Command docker -ErrorAction SilentlyContinue
    if ($cmd) {
        return $cmd.Source
    }

    $default = 'C:\Program Files\Docker\Docker\resources\bin\docker.exe'
    if (Test-Path $default) {
        return $default
    }

    throw 'Docker CLI not found. Install Docker Desktop and try again.'
}

function Require-DockerEngine {
    param([string]$DockerExe)

    $psi = New-Object System.Diagnostics.ProcessStartInfo
    $psi.FileName = $DockerExe
    $psi.Arguments = 'version'
    $psi.RedirectStandardOutput = $true
    $psi.RedirectStandardError = $true
    $psi.UseShellExecute = $false
    $psi.CreateNoWindow = $true

    $process = [System.Diagnostics.Process]::Start($psi)
    if (-not $process.WaitForExit(8000)) {
        $process.Kill()
        throw 'Docker engine is not running. Open Docker Desktop and wait for Engine running.'
    }

    if ($process.ExitCode -ne 0) {
        throw 'Docker engine is not running. Open Docker Desktop and wait for Engine running.'
    }
}

function Ensure-SailEnv {
    $envPath = Join-Path $PSScriptRoot '.env'
    if (-not (Test-Path $envPath)) {
        throw '.env not found. Copy .env.example and merge .env.docker.example first.'
    }

    if (-not $env:APP_PORT) { $env:APP_PORT = '8080' }
    if (-not $env:WWWGROUP) { $env:WWWGROUP = '1000' }
    if (-not $env:WWWUSER) { $env:WWWUSER = '1000' }
    if (-not $env:FORWARD_DB_PORT) { $env:FORWARD_DB_PORT = '3307' }
    if (-not $env:FORWARD_REDIS_PORT) { $env:FORWARD_REDIS_PORT = '6380' }
}

try {
    $Docker = Resolve-DockerCli
    Require-DockerEngine -DockerExe $Docker
    Ensure-SailEnv
    Push-Location $PSScriptRoot

    function Invoke-Compose {
        param([string[]]$ComposeArgs)
        $allArgs = @('compose') + $ComposeArgs
        $prevErrorAction = $ErrorActionPreference
        $ErrorActionPreference = 'Continue'
        & $Docker @allArgs 2>&1 | ForEach-Object { Write-Host $_ }
        $exitCode = $LASTEXITCODE
        $ErrorActionPreference = $prevErrorAction
        if ($exitCode -ne 0) {
            throw "docker compose $($ComposeArgs -join ' ') failed (exit $exitCode)"
        }
    }

    function Ensure-Up {
        Invoke-Compose @('up', '-d')
    }

    switch ($Action) {
        'build' { Invoke-Compose @('build') }
        'up' { Invoke-Compose @('up', '-d') }
        'down' { Invoke-Compose @('down') }
        'redis' { Invoke-Compose @('up', '-d', 'redis') }
        'setup' {
            Ensure-Up
            Invoke-Compose @('exec', '-T', $AppService, 'composer', 'install', '--no-interaction')
            Invoke-Compose @('exec', '-T', $AppService, 'php', 'artisan', 'key:generate', '--force')
            Invoke-Compose @('exec', '-T', $AppService, 'php', 'artisan', 'migrate', '--seed', '--force')
            Write-Host ''
            Write-Host 'Sail app ready: http://localhost:8080/login' -ForegroundColor Green
            Write-Host 'Login: admin@terrastay.com / password'
        }
        'migrate' {
            Ensure-Up
            Invoke-Compose @('exec', '-T', $AppService, 'php', 'artisan', 'migrate', '--seed', '--force')
        }
        'test' {
            Ensure-Up
            Invoke-Compose @('exec', '-T', $AppService, 'php', 'artisan', 'test')
        }
        'pint' {
            Ensure-Up
            Invoke-Compose @('exec', '-T', $AppService, './vendor/bin/pint', '--test')
        }
        'logs' { Invoke-Compose @('logs', '-f') }
        'sh' { Ensure-Up; Invoke-Compose @('exec', $AppService, 'bash') }
        'shell' { Ensure-Up; Invoke-Compose @('exec', $AppService, 'bash') }
    }
} catch {
    Write-Host $_.Exception.Message -ForegroundColor Red
    exit 1
} finally {
    Pop-Location
}
