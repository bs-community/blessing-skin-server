#!/bin/bash
set -e
mkdir dist
cd dist
cp ../blessing-skin-server-$RELEASE_TAG.zip blessing-skin-server-$RELEASE_TAG.zip
echo "{\"spec\":1,\"latest\":\"$RELEASE_TAG\",\"url\":\"\"}" > update.json
git init
git add .
git commit -m "Publish"
git remote add origin https://blessing-skin:$AZURE_TOKEN@dev.azure.com/blessing-skin/Blessing%20Skin%20Server/_git/Blessing%20Skin%20Server
git push -f origin master
