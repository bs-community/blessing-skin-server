/*
* @Author: printempw
* @Date:   2016-07-21 13:38:26
 * @Last Modified by: g-plane
 * @Last Modified time: 2017-04-26 15:39:46
*/

'use strict';

var gulp     = require('gulp'),
    babel    = require('gulp-babel'),
    elixir   = require('laravel-elixir'),
    uglify   = require('gulp-uglify'),
    sass     = require('gulp-sass'),
    cleanCss = require('gulp-clean-css'),
    del      = require('del'),
    zip      = require('gulp-zip'),
    notify   = require('gulp-notify');

require('laravel-elixir-replace');

var version  = require('./package.json').version;

var srcPath  = 'resources/assets/src/';
var distPath = 'resources/assets/dist/';

var vendorScripts = [
    'jquery/dist/jquery.min.js',
    'bootstrap/dist/js/bootstrap.min.js',
    'admin-lte/dist/js/app.min.js',
    'bootstrap-fileinput/js/fileinput.min.js',
    'admin-lte/plugins/datatables/jquery.dataTables.min.js',
    'admin-lte/plugins/datatables/dataTables.bootstrap.min.js',
    'icheck/icheck.min.js',
    'toastr/build/toastr.min.js',
    'es6-promise/dist/es6-promise.auto.min.js',
    'sweetalert2/dist/sweetalert2.min.js',
    'jqPaginator/dist/1.2.0/jqPaginator.min.js',
    'resources/assets/dist/scripts/general.js',
];

var vendorStyles = [
    'bootstrap/dist/css/bootstrap.min.css',
    'admin-lte/dist/css/AdminLTE.min.css',
    'admin-lte/plugins/datatables/dataTables.bootstrap.css',
    'bootstrap-fileinput/css/fileinput.min.css',
    'font-awesome/css/font-awesome.min.css',
    'icheck/skins/square/blue.css',
    'toastr/build/toastr.min.css',
    'sweetalert2/dist/sweetalert2.min.css',
];

var replacements = [
    ['blue.png', '"../images/blue.png"'],
    ['blue@2x.png', '"../images/blue@2x.png"'],
    ['../img/loading.gif', '"../images/loading.gif"'],
    ['../img/loading-sm.gif', '"../images/loading-sm.gif"'],
    ['@import url(https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic);', ''],
];

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

elixir.config.sourcemaps = false;

elixir((mix) => {
    mix // compile sass files & ES6 scripts first
        .task('compile-es6')
        .task('compile-sass')

        .scripts(convertNpmRelativePath(vendorScripts), distPath + 'scripts/app.min.js', './')
        .styles(convertNpmRelativePath(vendorStyles),   distPath + 'styles/app.min.css', './')
        .replace(distPath + 'styles/app.min.css', replacements)

        // copy fonts & images
        .copy(convertNpmRelativePath(fonts),  distPath + 'fonts/')
        .copy(convertNpmRelativePath(images), distPath + 'images/')
        .copy(convertNpmRelativePath(['admin-lte/dist/css/skins']), distPath + 'styles/skins')
        .copy(
            ['skin-preview/**', 'Chart.min.js'].map(relativePath => `${srcPath}vendor/${relativePath}`),
            distPath + 'scripts/'
        );
});

// compile sass
gulp.task('compile-sass', () => {
    gulp.src(srcPath + 'styles/*.scss')
        .pipe(sass().on('error', sass.logError))
        .pipe(cleanCss())
        .pipe(gulp.dest(distPath + 'styles'));
});

gulp.task('compile-es6', () => {
    gulp.src(srcPath + 'scripts/*.js')
        .pipe(babel({ presets: ['es2015'] }))
        .pipe(uglify())
        .pipe(gulp.dest(distPath + 'scripts'));
});

gulp.task

// delete cache files
gulp.task('clear', () => {
    clearCache();
    clearDist();
});

// release
gulp.task('zip', () => {
    clearCache();

    console.info("============================================================================")
    console.info("= Don't forget to compile Sass & ES2015 files before publishing a release! =");
    console.info("============================================================================")

    let zipPath = `blessing-skin-server-v${version}.zip`;

    console.log(`Zip archive will be saved to ${zipPath}.`);

    return gulp.src([
            '**/*.*',
            'LICENSE',
            '!tests/**/*.*',
            '!node_modules/**/*.*',
            '!storage/textures/**/*.*',
            '!.env',
            '!.bowerrc',
            '!.gitignore',
            '!.git/**/*.*',
            '!.git/',
            '!.gitmodules',
            '!.gitattributes',
            '!artisan',
            '!gulpfile.js',
            '!package.json',
            '!composer.json',
            '!composer.lock',
            '!bower.json',
            '!plugins/**/*.*',
            '!resources/assets/src/**/*.*',
            // do not pack packages for developments
            '!vendor/fzaninotto/**/*.*',
            '!vendor/mockery/**/*.*',
            '!vendor/phpunit/**/*.*',
            '!vendor/symfony/css-selector/**/*.*',
            '!vendor/symfony/dom-crawler/**/*.*'
        ], { dot: true })
        .pipe(zip(zipPath))
        .pipe(gulp.dest('../'))
        .pipe(notify({ message: `Zip archive saved to ${zipPath}!` }));
});

gulp.task('watch', () => {
    // watch .scss files
    gulp.watch(srcPath + 'styles/*.scss', ['compile-sass'], () => notify({ message: 'Sass files compiled!' }));
    // watch .js files
    gulp.watch(srcPath + 'scripts/*.js', ['compile-es6'], () => notify({ message: 'ES6 scripts compiled!' }));
    gulp.watch(srcPath + 'scripts/general.js', ['scripts']);
});

function convertNpmRelativePath(paths) {
    return paths.map(relativePath => {
        return relativePath.startsWith('resources') ? relativePath : ('node_modules/' + relativePath);
    });
}

function clearCache() {
    return del([
        'storage/logs/*',
        'storage/debugbar/*',
        'storage/update_cache/*',
        'storage/yaml-translation/*',
        'storage/framework/cache/*',
        'storage/framework/sessions/*',
        'storage/framework/views/*'
    ]);
}

function clearDist() {
    return del([
        distPath + '**/*'
    ]);
}
