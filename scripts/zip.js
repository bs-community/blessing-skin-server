const fs = require('fs');
const archiver = require('archiver');
const { version } = require('../package.json');

module.exports = function () {
    const list = fs.readFileSync('./zip.txt', 'utf-8').split('\n');
    list.pop();  // Remove the empty line

    const output = fs.createWriteStream(process.argv[2] || `../blessing-skin-server-v${version}.zip`);
    const archive = archiver('zip', { zlib: { level: 9 } });

    return new Promise((resolve, reject) => {
        output.on('close', resolve);
        archive.on('warning', reject);
        archive.on('error', reject);

        archive.pipe(output);

        list.forEach(item => {
            if (item.endsWith('/')) {
                archive.directory(item, item);
            } else {
                archive.file(item, { name: item });
            }
        });

        archive.glob('storage/**/*.*', {
            dot: true,
            ignore: [
                'storage/textures/*',
            ],
        });

        archive.finalize();
    });
};

module.exports();
