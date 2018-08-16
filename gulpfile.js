'use strict';

const
    babel       = require('gulp-babel'),
    chalk       = require('chalk'),
    cleanCss    = require('gulp-clean-css'),
    concat      = require('gulp-concat'),
    del         = require('del'),
    eslint      = require('gulp-eslint'),
    execSync    = require('child_process').execSync,
    gulp        = require('gulp'),
    merge       = require('merge2'),
    replace     = require('gulp-batch-replace'),
    runSequence = require('run-sequence'),
    sourcemaps  = require('gulp-sourcemaps'),
    stylus      = require('gulp-stylus'),
    through2    = require('through2'),
    uglify      = require('gulp-uglify'),
    zip         = require('gulp-zip');

const version  = require('./package.json').version;

const srcPath  = 'resources/assets/src';
const distPath = 'resources/assets/dist';

const vendorScripts = [
    'jquery/dist/jquery.min.js',
    'bootstrap/dist/js/bootstrap.min.js',
    'admin-lte/dist/js/adminlte.min.js',
    'bootstrap-fileinput/js/fileinput.min.js',
    'icheck/icheck.min.js',
    'toastr/build/toastr.min.js',
    'es6-promise/dist/es6-promise.auto.min.js',
    'sweetalert2/dist/sweetalert2.min.js',
    'jqPaginator/dist/1.2.0/jqPaginator.min.js',
];

const vendorScriptsToBeMinified = [
    'regenerator-runtime/runtime.js',
    'datatables.net/js/jquery.dataTables.js',
    'datatables.net-bs/js/dataTables.bootstrap.js',
    'resources/assets/dist/js/common.js',
];

const vendorStyles = [
    'bootstrap/dist/css/bootstrap.min.css',
    'admin-lte/dist/css/AdminLTE.min.css',
    'datatables.net-bs/css/dataTables.bootstrap.css',
    'bootstrap-fileinput/css/fileinput.min.css',
    'font-awesome/css/font-awesome.min.css',
    'icheck/skins/square/blue.css',
    'toastr/build/toastr.min.css',
    'sweetalert2/dist/sweetalert2.min.css',
];

const styleReplacements = [
    ['blue.png', '"../images/blue.png"'],
    ['blue@2x.png', '"../images/blue@2x.png"'],
    ['../img/loading.gif', '"../images/loading.gif"'],
    ['../img/loading-sm.gif', '"../images/loading-sm.gif"'],
];

const scriptReplacements = [];

const fonts = [
    'font-awesome/fonts/**',
    'bootstrap/dist/fonts/**',
    'resources/assets/src/fonts/**',
];

const images = [
    'icheck/skins/square/blue.png',
    'icheck/skins/square/blue@2x.png',
    'resources/assets/src/images/**',
    'bootstrap-fileinput/img/loading.gif',
    'bootstrap-fileinput/img/loading-sm.gif',
];

const argv = require('minimist')(process.argv.slice(2));

// Determine if we are in production mode,
// run `gulp [task] --production` to enable.
if (argv.production) {
    console.log(chalk.green('>> Running in PRODUCTION mode <<'));
    process.env.NODE_ENV = 'production';
}

// aka. `yarn run build`
gulp.task('default', ['build']);

// Build the things!
gulp.task('build', callback => {
    runSequence('clean', 'lint', ['compile-scripts', 'compile-stylus'], 'publish-vendor', callback);
});

// Check JavaScript files with ESLint
gulp.task('lint', () => {
    return gulp.src(`${srcPath}/js/**/*.js`)
        .pipe(eslint())
        .pipe(eslint.format())
        .pipe(eslint.failAfterError());
});

// Concentrate all vendor scripts & styles to one dist file
gulp.task('publish-vendor', callback => {
    // Collect pre-complied and raw library files
    const vendorJs = gulp.src(collect(vendorScripts)).pipe(replace(scriptReplacements));
    const rawVendorJs = gulp.src(collect(vendorScriptsToBeMinified)).pipe(uglify());
    // JavaScript files
    merge(vendorJs, rawVendorJs)
        .pipe(sourcemaps.init({ loadMaps: true }))
        .pipe(concat('app.js'))
        // Remove source mappings in the pre-compiled files
        .pipe(sourcemaps.write({ addComment: false }))
        .pipe(gulp.dest(`${distPath}/js/`));
    // CSS files
    gulp.src(collect(vendorStyles))
        .pipe(sourcemaps.init({ loadMaps: true }))
        .pipe(concat('style.css'))
        .pipe(replace(styleReplacements))
        .pipe(sourcemaps.write({ addComment: false }))
        .pipe(gulp.dest(`${distPath}/css/`));
    // Fonts
    gulp.src(collect(fonts))
        .pipe(gulp.dest(`${distPath}/fonts/`));
    // Images
    gulp.src(collect(images))
        .pipe(gulp.dest(`${distPath}/images/`));
    // AdminLTE skins
    gulp.src(collect(['admin-lte/dist/css/skins/*.min.css']))
        .pipe(gulp.dest(`${distPath}/css/skins/`));
    // Libraries for 3D skin preview
    gulp.src(collect(['three/build/three.min.js', 'skinview3d/build/skinview3d.min.js']))
        .pipe(concat('skinview3d.js'))
        .pipe(gulp.dest(`${distPath}/js/`));
    // Chart.js
    gulp.src(collect(['chart.js/dist/Chart.min.js']))
        .pipe(concat('chart.js'))
        .pipe(gulp.dest(`${distPath}/js/`));

    callback();
});

// Compile stylus to css
gulp.task('compile-stylus', () => {
    return gulp.src(`${srcPath}/stylus/*.styl`)
        .pipe(dev(sourcemaps.init()))
        .pipe(stylus())
        .pipe(cleanCss())
        .pipe(dev(sourcemaps.write('./maps')))
        .pipe(gulp.dest(`${distPath}/css`));
});

// Compile ES6 scripts to ES5
gulp.task('compile-scripts', callback => {
    ['common', 'admin', 'auth', 'skinlib', 'user'].forEach(moduleName => {
        return gulp.src(`${srcPath}/js/${moduleName}/*.js`)
            .pipe(dev(sourcemaps.init()))
            .pipe(babel())
            .pipe(concat(`${moduleName}.js`))
            .pipe(uglify())
            .pipe(dev(sourcemaps.write('./maps')))
            .pipe(gulp.dest(`${distPath}/js`));
    });

    callback();
});

// Delete cache and built files
gulp.task('clean', callback => {
    del([`${distPath}/**/*`]);
    clearCache();
    callback();
});

// Release a zip archive file
// aka. `yarn run release`
gulp.task('zip', () => {
    console.log(`Don't forget to run ${ chalk.underline.yellow('gulp build --production') } first!`);

    console.log('Cleaning cache files');
    clearCache();

    // Generate autoload files without autoload-dev
    execSync('composer dump-autoload --no-dev', { stdio: 'inherit' });

    const savePath = argv['save-to'] || '..';
    const zipFile = `blessing-skin-server-v${version}.zip`;

    console.log('Zip archive will be saved to ' + chalk.underline.blue(
        require('path').join(savePath, zipFile)
    ));

    return gulp.src([
        '**/*',
        '**/.gitignore',
        '**/.htaccess',
        '.env.example',
        // Exclude unnecessary files
        '!.gitignore',
        '!composer.*',
        '!gulpfile.js',
        '!ISSUE_TEMPLATE.md',
        '!package.json',
        '!phpunit.xml',
        '!yarn.lock',
        // Exclude unnecessary directories
        '!plugins/**',
        '!resources/assets/{src,src/**}',
        '!resources/assets/dist/**/{maps,maps/**}',
        '!resources/lang/overrides/**',
        '!resources/views/overrides/**',
        '!storage/textures/**',
        '!{coverage,coverage/**}',
        '!{node_modules,node_modules/**,node_modules/**/.gitignore}',
        '!{tests,tests/**}',
        // Extracted symbol links are always weird, I don't know exactly why
        '!vendor/bin/**',
        // Exclude "require-dev" packages
        '!vendor/fzaninotto/**',
        '!vendor/mikey179/**',
        '!vendor/mockery/**',
        '!vendor/phpunit/**',
        '!vendor/symfony/css-selector/**',
        '!vendor/symfony/dom-crawler/**',
    ])
    .pipe(zip(zipFile))
    .pipe(gulp.dest(savePath))
    .pipe(through2.obj(function (chunk, enc, callback) {
        console.log('Zip archive saved!');
        // Generate autoload files with autoload-dev
        execSync('composer dump-autoload', { stdio: 'inherit' });
        callback();
    }));
});

gulp.task('watch', ['compile-stylus', 'compile-scripts'], () => {
    gulp.watch(`${srcPath}/stylus/*.styl`, ['compile-stylus']);
    gulp.watch(`${srcPath}/js/**/*.js`, ['compile-scripts']);
    gulp.watch(`${srcPath}/js/common/*.js`, ['publish-vendor']);
});

function dev(transformFunction) {
    return argv.production ? through2.obj() : transformFunction;
}

const collect = function convertNpmRelativePath(paths) {
    return paths.map(relativePath => {
        return relativePath.startsWith('resources') ? relativePath : `node_modules/${relativePath}`;
    });
};

function clearCache() {
    return del([
        'storage/logs/*.log',
        'storage/testing/*',
        'storage/debugbar/*',
        'storage/update_cache/*',
        'storage/update_cache',
        'storage/yaml-translation/*',
        'storage/framework/cache/*',
        'storage/framework/sessions/*',
        'storage/framework/views/*',
        '!storage/framework/sessions/index.html'
    ]);
}
