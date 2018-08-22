const Listr = require('listr');
const execa = require('execa');
const rimraf = require('rimraf');

function del(path) {
    return new Promise((resolve, reject) => {
        rimraf(path, err => err ? reject(err) : resolve());
    });
}

const tasks = new Listr([
    {
        title: 'Clear cache files',
        task: async () => await Promise.all([
            'bootstrap/cache/*.php',
            'storage/logs/*.log',
            'storage/testing/*',
            'storage/debugbar/*',
            'storage/update_cache/*',
            'storage/update_cache',
            'storage/yaml-translation/*',
            'storage/framework/cache/*',
            'storage/framework/sessions/*',
            'storage/framework/views/*',
        ].map(del))
    },
    {
        title: 'Install PHP dependencies for production',
        task: async () => {
            await del('vendor');
            await execa('composer', ['install', '--no-dev']);
            return del('vendor/bin');
        }
    },
    {
        title: 'Build the zip archive',
        task: require('./zip')
    },
    {
        title: 'Install PHP dependencies for development',
        task: () => execa('composer', ['install'])
    },
]);

tasks.run().catch(console.error);
