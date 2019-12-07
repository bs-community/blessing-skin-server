$botRelease = (Invoke-WebRequest 'https://api.github.com/repos/bs-community/telegram-bot/releases/latest').Content | ConvertFrom-Json
$botBinUrl = ((Invoke-WebRequest $botRelease.assets_url).Content | ConvertFrom-Json).browser_download_url

bash -c "curl -fSL $botBinUrl -o bot"
chmod +x ./bot
./bot diff
