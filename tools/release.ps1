$manifest = Invoke-WebRequest 'https://dev.azure.com/blessing-skin/51010f6d-9f99-40f1-a262-0a67f788df32/_apis/git/repositories/a9ff8df7-6dc3-4ff8-bb22-4871d3a43936/Items?path=%2Fupdate.json' | ConvertFrom-Json
$last = $manifest.latest
$current = (Get-Content package.json | ConvertFrom-Json).version

# Install dependencies
composer install --no-dev --prefer-dist --no-progress
Remove-Item vendor/bin -Recurse -Force
yarn
Write-Host "Dependencies have been installed." -ForegroundColor Green
./tools/build.ps1

$zip = "blessing-skin-server-$current.zip"
zip -9 -r $zip app bootstrap config database plugins public resources/lang resources/views resources/misc/textures routes storage vendor .env.example artisan LICENSE README.md README-zh.md index.html
Write-Host "Zip archive is created." -ForegroundColor Green

New-Item dist -ItemType Directory
Set-Location dist
Copy-Item -Path "../$zip" -Destination $zip

$manifest.latest = $current
$manifest.url = $manifest.url.Replace($last, $current)
$manifest.php = '8.0.2'
ConvertTo-Json $manifest | Out-File -FilePath update.json
Write-Host "Update source is prepared." -ForegroundColor Green

$azureToken = $env:AZURE_TOKEN
git config --global user.email 'g-plane@hotmail.com'
git config --global user.name 'Pig Fang'
git init
git add .
git commit -m "Publish"
git push -f "https://anything:$azureToken@dev.azure.com/blessing-skin/Blessing%20Skin%20Server/_git/Blessing%20Skin%20Server" master
Write-Host "Update source is pushed to Azure Repos." -ForegroundColor Green
