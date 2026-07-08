param(
    [Parameter(Position = 0)]
    [ValidateSet('build', 'up', 'down', 'setup', 'migrate', 'test', 'pint', 'redis', 'logs', 'sh', 'shell')]
    [string]$Action = 'up'
)

& (Join-Path $PSScriptRoot 'sail.ps1') -Action $Action
