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

New-Item -ItemType Directory ./public/app/brand-icons
Copy-Item -Path ./node_modules/@fortawesome/fontawesome-free/svgs/brands/*.svg -Destination ./public/app/brand-icons

if ($Simple) {
    exit
}

# Copy static files
Copy-Item -Path ./resources/assets/src/images/bg.png -Destination ./public/app
Copy-Item -Path ./resources/assets/src/images/favicon.ico -Destination ./public/app
Write-Host 'Static files copied.' -ForegroundColor Green

# Write commit ID
$commit = git log --pretty=%H -1
$manifest = Get-Content ./public/app/manifest.json | ConvertFrom-Json
$manifest | Add-Member -MemberType NoteProperty -Name commit -Value $commit.Trim()
ConvertTo-Json $manifest | Set-Content ./public/app/manifest.json
Write-Host 'Saved commit ID.' -ForegroundColor Green
