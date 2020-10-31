param (
    # Clean files and run webpack only.
    [Parameter()]
    [switch]
    $Simple
)

# Clean files
if (Test-Path ./public/app) {
    Remove-Item ./public/app -Recurse -Force
}

# Run webpack
yarn build

if ($Simple) {
    exit
}

# Copy static files
Copy-Item -Path ./resources/assets/src/images/bg.webp -Destination ./public/app
Copy-Item -Path ./resources/assets/src/images/favicon.ico -Destination ./public/app
Write-Host 'Static files copied.' -ForegroundColor Green
