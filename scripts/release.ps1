$manifest = Invoke-WebRequest 'https://dev.azure.com/blessing-skin/51010f6d-9f99-40f1-a262-0a67f788df32/_apis/git/repositories/a9ff8df7-6dc3-4ff8-bb22-4871d3a43936/Items?path=%2Fupdate_2.json' | ConvertFrom-Json
$last = $manifest.latest
$current = (Get-Content package.json | ConvertFrom-Json).version

Write-Host "Latest version: $last. Current: $current" -ForegroundColor Blue

if ($last -eq $current) {
    Write-Host "Latest version is $last. No need to publish." -ForegroundColor Green -BackgroundColor DarkMagenta
    exit
}

Install-Module PSGitHub -Force
Write-Host "'PSGitHub' has been installed." -ForegroundColor Green

# Install dependencies
composer install --no-dev --prefer-dist --no-suggest --no-progress
Remove-Item vendor/bin -Recurse -Force
yarn
yarn build
Write-Host "Dependencies have been installed." -ForegroundColor Green

$zip = "blessing-skin-server-$current.zip"
zip -9 -r $zip app bootstrap config database plugins public resources/lang resources/views resources/misc routes storage vendor .env.example artisan LICENSE README.md README_EN.md
Write-Host "Zip archive is created." -ForegroundColor Green

New-Item dist -ItemType Directory
Set-Location dist
Copy-Item -Path "../$zip" -Destination $zip

$manifest.latest = $current
$manifest.url = $manifest.url.Replace($last, $current)
$manifest.php = '7.2.0'
ConvertTo-Json $manifest | Out-File -FilePath update_preview.json
Write-Host "Update source is prepared." -ForegroundColor Green

$azureToken = $env:AZURE_TOKEN
git config --global user.email 'g-plane@hotmail.com'
git config --global user.name 'Pig Fang'
git init
git add .
git commit -m "Publish"
git push -f "https://anything:$azureToken@dev.azure.com/blessing-skin/Blessing%20Skin%20Server/_git/Blessing%20Skin%20Server" master
Write-Host "Update source is pushed to Azure Repos." -ForegroundColor Green

$githubToken = $env:GITHUB_TOKEN | ConvertTo-SecureString -AsPlainText -Force
$changelogPath = "../resources/misc/changelogs/en/$current.md"
if (Test-Path $changelogPath) {
    $enChangelog = Get-Content $changelogPath
    $changelog = "`n---`n" + $enChangelog
} else {
    $changelog = ''
}
$release = New-GitHubRelease -Token $githubToken -Owner 'bs-community' -Repository 'blessing-skin-server' -TagName $current -ReleaseNote $changelog -PreRelease
try {
    New-GitHubReleaseAsset -Token $githubToken -Owner 'bs-community' -Repository 'blessing-skin-server'  -ReleaseId $release.Id -Path $zip
} catch {
    # Do nothing.
}
Write-Host "New version $current is published!" -ForegroundColor Green -BackgroundColor DarkMagenta
