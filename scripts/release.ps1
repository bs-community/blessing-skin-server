$manifest = Invoke-WebRequest 'https://dev.azure.com/blessing-skin/51010f6d-9f99-40f1-a262-0a67f788df32/_apis/git/repositories/a9ff8df7-6dc3-4ff8-bb22-4871d3a43936/Items?path=%2Fupdate_2.json' | ConvertFrom-Json
$last = $manifest.latest
$current = (Get-Content package.json | ConvertFrom-Json).version

if ($last -eq $current) {
    exit
}

Install-Module PSGitHub -Force

# Install dependencies
composer install --no-dev
Remove-Item vendor/bin -Recurse -Force
yarn
yarn build

$zip = "blessing-skin-server-$current.zip"
zip -9 -r $zip app bootstrap config database plugins public resources/lang resources/views resources/misc routes storage vendor .env.example artisan LICENSE README.md README_EN.md

New-Item dist -ItemType Directory
Set-Location dist
Copy-Item -Path "../$zip" -Destination $zip

$manifest.latest = $current
$manifest.url = $manifest.url.Replace($last, $current)
ConvertTo-Json $manifest | Out-File -FilePath update_2.json

$azureToken = $env:AZURE_TOKEN
git config --global user.email 'g-plane@hotmail.com'
git config --global user.name 'Pig Fang'
git init
git add .
git commit -m "Publish"
git push -f "https://anything:$azureToken@dev.azure.com/blessing-skin/Blessing%20Skin%20Server/_git/Blessing%20Skin%20Server" master

$githubToken = $env:GITHUB_TOKEN | ConvertTo-SecureString -AsPlainText -Force
$enChangelog = Get-Content "../resources/misc/changelogs/en/$current.md"
$changelog = "`n---`n" + $enChangelog
New-GitHubRelease -Token $githubToken -Owner 'bs-community' -Repository 'blessing-skin-server' -TagName $current -ReleaseNote $changelog
