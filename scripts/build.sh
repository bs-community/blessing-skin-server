#!/bin/bash
rm -rf ./public/app
yarn build

#TODO：if ($Simple)

cp ./resources/assets/src/images/bg.webp ./public/app
cp ./resources/assets/src/images/favicon.ico ./public/app
echo "Static files copied."

commit=`git log --pretty=%H -1`
node -p "JSON.stringify({...require('./public/app/manifest.json'), commit: '$commit'}, null, '\t')" > temp_data.json
mv -f temp_data.json public/app/manifest.json
echo "Saved commit ID."
