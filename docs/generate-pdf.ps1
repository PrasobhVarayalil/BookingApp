$ErrorActionPreference = 'Stop'
$docs = Split-Path -Parent $MyInvocation.MyCommand.Path
$html = Join-Path $docs 'DEVELOPER_REFERENCE.html'
$pdf = Join-Path $docs 'DEVELOPER_REFERENCE.pdf'

$chrome = @(
    "${env:ProgramFiles}\Google\Chrome\Application\chrome.exe",
    "${env:ProgramFiles(x86)}\Microsoft\Edge\Application\msedge.exe"
) | Where-Object { Test-Path $_ } | Select-Object -First 1

if (-not $chrome) {
    throw 'Chrome or Edge not found for PDF generation.'
}

if (-not (Test-Path $html)) {
    throw "HTML source not found: $html"
}

if (Test-Path $pdf) {
    Remove-Item $pdf -Force
}

$ErrorActionPreference = 'Continue'

& $chrome --headless=new --disable-gpu --no-pdf-header-footer `
    --print-to-pdf="$pdf" "file:///$($html.Replace('\','/'))" 2>&1 | Out-Null

$deadline = (Get-Date).AddSeconds(10)
while (-not (Test-Path $pdf) -and (Get-Date) -lt $deadline) {
    Start-Sleep -Milliseconds 250
}

if (-not (Test-Path $pdf)) {
    throw 'PDF generation failed.'
}

Write-Host "Created: $pdf"
