const fs = require('fs');
const path = require('path');
const AdmZip = require('adm-zip');
const { version } = require('../package.json');

module.exports = function () {
    const list = fs.readFileSync('./zip.txt', 'utf-8').split('\n');
    list.pop();  // Remove the empty line

    const archive = new AdmZip();

    list.forEach(item => {
        if (item.endsWith('/')) {
            archive.addLocalFolder(item, item);
        } else {
            archive.addLocalFile(item);
        }
    });

    archive.writeZip(`../blessing-skin-server-v${version}.zip`);
};
