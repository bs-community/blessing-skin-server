#!/bin/bash
set -e
mkdir dist
cd dist
cp ../blessing-skin-server-$RELEASE_TAG.zip blessing-skin-server-$RELEASE_TAG.zip
echo "{\"spec\":1,\"latest\":\"$RELEASE_TAG\",\"url\":\"https://dev.azure.com/blessing-skin/51010f6d-9f99-40f1-a262-0a67f788df32/_apis/git/repositories/a9ff8df7-6dc3-4ff8-bb22-4871d3a43936/Items?path=%2Fblessing-skin-server-$RELEASE_TAG.zip\"}" > update.json
git init
git add .
git commit -m "Publish"
git remote add origin https://blessing-skin:$AZURE_TOKEN@dev.azure.com/blessing-skin/Blessing%20Skin%20Server/_git/Blessing%20Skin%20Server
git push -f origin master
