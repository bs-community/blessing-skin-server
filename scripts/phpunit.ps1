param (
    [Parameter(Position = 0)]
    [string]
    $Filter,

    [Parameter()]
    [switch]
    $Bail,

    # For CI only
    [Parameter()]
    [switch]
    $Coverage
)

$dbPath = [System.IO.Path]::GetTempPath() + 'bs.db'
$env:DB_CONNECTION = 'sqlite'
$env:DB_DATABASE = $dbPath

if (Test-Path $dbPath) {
    Remove-Item $dbPath
}
New-Item $dbPath | Out-Null

$arguments = ''
if ($Filter) {
    $arguments += " --filter=$Filter"
}
if ($Bail) {
    $arguments += ' --stop-on-failure'
}
if ($Coverage) {
    $arguments += ' --coverage-clover=coverage.xml'
}

./vendor/bin/phpunit $arguments
