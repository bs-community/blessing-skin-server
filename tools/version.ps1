param (
    # New Blessing Skin version
    [Parameter(Position = 0)]
    [ValidatePattern('^\d+\.\d+\.\d+(?:-(?:alpha|beta|rc)\.\d+)?$')]
    [string]
    $NewVersion
)

$current = (Get-Content ./package.json | ConvertFrom-Json).version

# Update files
(Get-Content ./package.json).Replace(
    "`"version`": `"$current`"",
    "`"version`": `"$NewVersion`""
) | Set-Content ./package.json
(Get-Content ./config/app.php).Replace($current, $NewVersion) | Set-Content ./config/app.php

# Run Git
git add ./package.json ./config/app.php
git commit -m "Bump version to $NewVersion"
git tag -a $NewVersion -m $NewVersion
git checkout master
git merge dev
git push --all --follow-tags
git checkout dev
