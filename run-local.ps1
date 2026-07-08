param(
    [Parameter(Position = 0)]
    [ValidateSet('up', 'down', 'fresh', 'status')]
    [string]$Action = 'up'
)

$ErrorActionPreference = 'Stop'

$AppHost = '127.0.0.1'
$AppPort = 8000
$RedisHome = Join-Path $env:LOCALAPPDATA 'Redis'
$RedisServer = Join-Path $RedisHome 'redis-server.exe'
$RedisCli = Join-Path $RedisHome 'redis-cli.exe'
$RedisConf = Join-Path $RedisHome 'redis.windows.conf'

function Test-Redis {
    $client = New-Object System.Net.Sockets.TcpClient
    try {
        $client.Connect('127.0.0.1', 6379)
        return $client.Connected
    } catch {
        return $false
    } finally {
        $client.Dispose()
    }
}

function Start-Redis {
    if (Test-Redis) {
        Write-Host 'Redis already running' -ForegroundColor Green
        return
    }
    if (-not (Test-Path $RedisServer)) {
        throw "Redis not found at $RedisServer. Install portable Redis or set CACHE_STORE=database in .env."
    }
    $args = if (Test-Path $RedisConf) { @("`"$RedisConf`"") } else { @() }
    Start-Process -FilePath $RedisServer -ArgumentList $args -WorkingDirectory $RedisHome -WindowStyle Hidden
    for ($i = 0; $i -lt 10; $i++) {
        Start-Sleep -Milliseconds 500
        if (Test-Redis) { Write-Host 'Redis started' -ForegroundColor Green; return }
    }
    throw 'Redis did not come up within 5 seconds.'
}

function Test-Server {
    try {
        $r = Invoke-WebRequest -Uri "http://${AppHost}:$AppPort/login" -UseBasicParsing -TimeoutSec 5
        return ($r.StatusCode -eq 200)
    } catch {
        return $false
    }
}

function Get-ServeProcess {
    Get-CimInstance Win32_Process -Filter "Name = 'php.exe'" -ErrorAction SilentlyContinue |
        Where-Object { $_.CommandLine -like '*artisan serve*' }
}

function Start-Server {
    if (Test-Server) {
        Write-Host "App already running: http://${AppHost}:$AppPort/login" -ForegroundColor Green
        return
    }
    Start-Process -FilePath 'php' `
        -ArgumentList 'artisan', 'serve', "--host=$AppHost", "--port=$AppPort" `
        -WorkingDirectory $PSScriptRoot -WindowStyle Hidden
    for ($i = 0; $i -lt 10; $i++) {
        Start-Sleep -Milliseconds 700
        if (Test-Server) { Write-Host "App started: http://${AppHost}:$AppPort/login" -ForegroundColor Green; return }
    }
    throw 'App server did not respond within ~7 seconds.'
}

function Stop-All {
    Get-Process -Name 'redis-server' -ErrorAction SilentlyContinue | Stop-Process -Force
    Get-ServeProcess | ForEach-Object { Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue }
    Write-Host 'Stopped Redis and app server' -ForegroundColor Yellow
}

try {
    switch ($Action) {
        'up' {
            Start-Redis
            Start-Server
            Write-Host ''
            Write-Host "Ready: http://${AppHost}:$AppPort/login" -ForegroundColor Green
            Write-Host 'Login: admin@terrastay.com / password'
        }
        'fresh' {
            Start-Redis
            & php artisan migrate:fresh --seed --force
            Start-Server
            Write-Host ''
            Write-Host "Ready (fresh DB): http://${AppHost}:$AppPort/login" -ForegroundColor Green
        }
        'down' { Stop-All }
        'status' {
            $redisState = if (Test-Redis) { 'running' } else { 'down' }
            $appState = if (Test-Server) { 'running' } else { 'down' }
            Write-Host "Redis: $redisState"
            Write-Host "App:   $appState  (http://${AppHost}:$AppPort/login)"
        }
    }
} catch {
    Write-Host $_.Exception.Message -ForegroundColor Red
    exit 1
}
