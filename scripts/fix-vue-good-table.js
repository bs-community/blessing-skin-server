const fs = require('fs');

const manifest = JSON.parse(fs.readFileSync('./node_modules/vue-good-table/package.json', 'utf-8'));
delete manifest.sideEffects;
fs.writeFileSync('./node_modules/vue-good-table/package.json', JSON.stringify(manifest, null, 2));
