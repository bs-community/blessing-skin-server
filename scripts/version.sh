#!/bin/bash
set -e
CURRENT=$(cat package.json | grep -P '\d+\.\d+\.\d+' -o | head -n 1)
NEW_VERSION=$(./node_modules/.bin/semver -i $1 $CURRENT)
sed -i "0,/$CURRENT/s/$CURRENT/$NEW_VERSION/" package.json config/app.php
git add package.json config/app.php
git commit -m "Bump version to $NEW_VERSION"
git tag -a $NEW_VERSION -m $NEW_VERSION
git checkout master
git merge dev
git push --all --follow-tags
git checkout dev
