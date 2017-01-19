/*
* @Author: printempw
* @Date:   2016-07-21 13:38:26
* @Last Modified by:   printempw
* @Last Modified time: 2017-01-19 23:07:05
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

let version  = require('./package.json').version;

let vendorJs = [
    'jquery/dist/jquery.min.js',
    'bootstrap/dist/js/bootstrap.min.js',
    'AdminLTE/dist/js/app.min.js',
    'bootstrap-fileinput/js/fileinput.min.js',
    'AdminLTE/plugins/datatables/jquery.dataTables.min.js',
    'AdminLTE/plugins/datatables/dataTables.bootstrap.min.js',
    'iCheck/icheck.min.js',
    'toastr/toastr.min.js',
    'es6-promise/es6-promise.auto.min.js',
    'sweetalert2/dist/sweetalert2.min.js',
];

let vendorCss = [
    'bootstrap/dist/css/bootstrap.min.css',
    'AdminLTE/dist/css/AdminLTE.min.css',
    'AdminLTE/plugins/datatables/dataTables.bootstrap.css',
    'bootstrap-fileinput/css/fileinput.min.css',
    'font-awesome/css/font-awesome.min.css',
    'iCheck/skins/square/blue.css',
    'toastr/toastr.min.css',
    'sweetalert2/dist/sweetalert2.min.css',
];

let replacements = [
    ['blue.png', '"../images/blue.png"'],
    ['blue@2x.png', '"../images/blue@2x.png"'],
    ['../fonts/glyphicons', '../fonts/glyphicons'],
    ['../fonts/fontawesome', '../fonts/fontawesome'],
    ['../img/loading.gif', '"../images/loading.gif"'],
    ['../img/loading-sm.gif', '"../images/loading-sm.gif"'],
    ['@import url(https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic);', ''],
];

let fonts = [
    'font-awesome/fonts/**',
    'bootstrap/dist/fonts/**',
];

let images = [
    'iCheck/skins/square/blue.png',
    'iCheck/skins/square/blue@2x.png',
    'bootstrap-fileinput/img/loading.gif',
    'bootstrap-fileinput/img/loading-sm.gif',
];

elixir.config.sourcemaps = false;

elixir((mix) => {
    mix // compile sass files & ES6 scripts first
        .task('compile-sass')
        .task('compile-es6')

        .scripts(convertRelativePath(vendorJs).concat([
            'resources/assets/dist/js/general.js'
        ]), 'resources/assets/dist/js/app.min.js', './')

        .styles(convertRelativePath(vendorCss), 'resources/assets/dist/css/app.min.css', './')
        .replace('resources/assets/dist/css/app.min.css', replacements)

        // copy fonts & images
        .copy(convertRelativePath(fonts), 'resources/assets/dist/fonts/')
        .copy(convertRelativePath(images), 'resources/assets/dist/images/');
});

// compile sass
gulp.task('compile-sass', () => {
    gulp.src('resources/assets/src/sass/*.scss')
        .pipe(sass().on('error', sass.logError))
        .pipe(cleanCss())
        .pipe(gulp.dest('./resources/assets/dist/css'));
});

gulp.task('compile-es6', () => {
    gulp.src('resources/assets/src/js/*.js')
        .pipe(babel({
            presets: ['es2015']
        }))
        .pipe(uglify())
        .pipe(gulp.dest('./resources/assets/dist/js'));
});

// delete cache files
gulp.task('clear', () => {
    clearCache();
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
            '!.gitattributes',
            '!artisan',
            '!koala-config.json',
            '!gulpfile.js',
            '!package.json',
            '!composer.json',
            '!composer.lock',
            '!bower.json',
            '!resources/assets/src/**/*.*',
            '!.sass-cache/**/*.*',
            '!.sass-cache/',
            // do not pack vendor for developments
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

gulp.task('notify')

gulp.task('watch', () => {
    // Watch .scss files
    gulp.watch('resources/assets/src/sass/*.scss', ['compile-sass'], () => notify({ message: 'Sass files compiled!' }));
    // Watch .js files
    gulp.watch('resources/assets/src/js/*.js', ['compile-es6'], () => notify({ message: 'ES6 scripts compiled!' }));
    gulp.watch('resources/assets/src/js/general.js', ['scripts']);
});

function convertRelativePath(paths) {
    return paths.map(relativePath => 'resources/assets/src/bower_components/' + relativePath);
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
