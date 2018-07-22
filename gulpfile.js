'use strict';

var gulp        = require('gulp'),
    babel       = require('gulp-babel'),
    eslint      = require('gulp-eslint'),
    uglify      = require('gulp-uglify'),
    stylus      = require('gulp-stylus'),
    cleanCss    = require('gulp-clean-css'),
    del         = require('del'),
    exec        = require('child_process').exec,
    concat      = require('gulp-concat'),
    zip         = require('gulp-zip'),
    replace     = require('gulp-batch-replace'),
    notify      = require('gulp-notify'),
    sourcemaps  = require('gulp-sourcemaps'),
    merge       = require('merge2'),
    runSequence = require('run-sequence');

var version  = require('./package.json').version;

var srcPath  = 'resources/assets/src';
var distPath = 'resources/assets/dist';

var vendorScripts = [
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

var vendorScriptsToBeMinified = [
    'regenerator-runtime/runtime.js',
    'datatables.net/js/jquery.dataTables.js',
    'datatables.net-bs/js/dataTables.bootstrap.js',
    'resources/assets/dist/js/common.js',
];

var vendorStyles = [
    'bootstrap/dist/css/bootstrap.min.css',
    'admin-lte/dist/css/AdminLTE.min.css',
    'datatables.net-bs/css/dataTables.bootstrap.css',
    'bootstrap-fileinput/css/fileinput.min.css',
    'font-awesome/css/font-awesome.min.css',
    'icheck/skins/square/blue.css',
    'toastr/build/toastr.min.css',
    'sweetalert2/dist/sweetalert2.min.css',
];

var styleReplacements = [
    ['blue.png', '"../images/blue.png"'],
    ['blue@2x.png', '"../images/blue@2x.png"'],
    ['../img/loading.gif', '"../images/loading.gif"'],
    ['../img/loading-sm.gif', '"../images/loading-sm.gif"'],
];

var scriptReplacements = [];

var fonts = [
    'font-awesome/fonts/**',
    'bootstrap/dist/fonts/**',
    'resources/assets/src/fonts/**',
];

var images = [
    'icheck/skins/square/blue.png',
    'icheck/skins/square/blue@2x.png',
    'resources/assets/src/images/**',
    'bootstrap-fileinput/img/loading.gif',
    'bootstrap-fileinput/img/loading-sm.gif',
];

// aka. `yarn run build`
gulp.task('default', ['build']);

// Build the things!
gulp.task('build', callback => {
    runSequence('clean', 'lint', ['compile-es6', 'compile-stylus'], 'publish-vendor', 'notify', callback);
});

// Send a notification
gulp.task('notify', () => {
    return gulp.src('').pipe(notify('Assets compiled!'));
});

// Check JavaScript files with ESLint
gulp.task('lint', () => {
    return gulp.src(`${srcPath}/js/**/*.js`)
        .pipe(eslint())
        .pipe(eslint.format())
        .pipe(eslint.failAfterError());
});

// Concentrate all vendor scripts & styles to one dist file
gulp.task('publish-vendor', ['compile-es6'], callback => {
    // JavaScript files
    var js = gulp.src(convertNpmRelativePath(vendorScripts))
        .pipe(replace(scriptReplacements));
    var jsToBeMinified = gulp.src(convertNpmRelativePath(vendorScriptsToBeMinified))
        .pipe(uglify());
    merge(js, jsToBeMinified)
        .pipe(concat('app.js'))
        .pipe(gulp.dest(`${distPath}/js/`));
    // CSS files
    gulp.src(convertNpmRelativePath(vendorStyles))
        .pipe(concat('style.css'))
        .pipe(replace(styleReplacements))
        .pipe(gulp.dest(`${distPath}/css/`));
    // Fonts
    gulp.src(convertNpmRelativePath(fonts))
        .pipe(gulp.dest(`${distPath}/fonts/`));
    // Images
    gulp.src(convertNpmRelativePath(images))
        .pipe(gulp.dest(`${distPath}/images/`));
    // AdminLTE skins
    gulp.src(convertNpmRelativePath(['admin-lte/dist/css/skins/*.min.css']))
        .pipe(gulp.dest(`${distPath}/css/skins/`));
    // 3D skin preview
    gulp.src(convertNpmRelativePath(['three/build/three.min.js', 'skinview3d/build/skinview3d.min.js']))
        .pipe(concat('skinview3d.js'))
        .pipe(gulp.dest(`${distPath}/js/`));
    // Chart.js
    gulp.src(convertNpmRelativePath(['chart.js/dist/Chart.min.js']))
        .pipe(concat('chart.js'))
        .pipe(gulp.dest(`${distPath}/js/`));

    callback();
});

// Compile stylus to css
gulp.task('compile-stylus', () => {
    return gulp.src(`${srcPath}/stylus/*.styl`)
        .pipe(sourcemaps.init())
        .pipe(stylus())
        .pipe(cleanCss())
        .pipe(sourcemaps.write('./maps'))
        .pipe(gulp.dest(`${distPath}/css`));
});

// Compile ES6 scripts to ES5
gulp.task('compile-es6', callback => {
    ['common', 'admin', 'auth', 'skinlib', 'user'].forEach(moduleName => {
        return gulp.src(`${srcPath}/js/${moduleName}/*.js`)
            .pipe(sourcemaps.init())
            .pipe(babel())
            .pipe(concat(`${moduleName}.js`))
            .pipe(uglify())
            .pipe(sourcemaps.write('./maps'))
            .pipe(gulp.dest(`${distPath}/js`));
    });

    callback();
});

// Delete cache files
gulp.task('clean', () => {
    clearCache();

    return clearDist();
});

// Release archive file
// aka. `yarn run release`
gulp.task('zip', () => {
    clearCache();
    console.log('Cache file deleted');

    exec('composer dump-autoload --no-dev', () => {
        console.log('Autoload files generated without autoload-dev');
    });

    let zipPath = `blessing-skin-server-v${version}.zip`;

    console.log(`Zip archive will be saved to ${zipPath}.`);

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
            // Exclud unnecessary directories
            '!plugins/**',
            '!resources/assets/{src,src/**}',
            '!resources/assets/dist/**/{maps,maps/**}',
            '!resources/lang/overrides/**',
            '!resources/views/overrides/**',
            '!storage/textures/**',
            '!{coverage,coverage/**}',
            '!{node_modules,node_modules/**,node_modules/**/.gitignore}',
            '!{tests,tests/**}',
            // Exclude require-dev packages
            '!vendor/fzaninotto/**',
            '!vendor/mikey179/**',
            '!vendor/mockery/**',
            '!vendor/phpunit/**',
            '!vendor/symfony/css-selector/**',
            '!vendor/symfony/dom-crawler/**',
        ])
        .pipe(zip(zipPath))
        .pipe(notify('Don\'t forget to build front-end resources before publishing a release!'))
        .pipe(gulp.dest('../'))
        .pipe(notify({ message: `Zip archive saved to ${zipPath}!` }));
});

gulp.task('watch', ['compile-stylus', 'compile-es6'], () => {
    // watch .scss files
    gulp.watch(`${srcPath}/stylus/*.scss`, ['compile-stylus'], () => notify('Stylus files compiled!'));
    // watch .js files
    gulp.watch(`${srcPath}/js/**/*.js`, ['compile-es6'], () => notify('ES6 scripts compiled!'));
    gulp.watch(`${srcPath}/js/general.js`, ['publish-vendor']);
});

function convertNpmRelativePath(paths) {
    return paths.map(relativePath => {
        return relativePath.startsWith('resources') ? relativePath : `node_modules/${relativePath}`;
    });
}

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
        'storage/framework/views/*'
    ]);
}

function clearDist() {
    return del([`${distPath}/**/*`]);
}
