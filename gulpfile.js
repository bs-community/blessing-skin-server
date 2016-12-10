/*
* @Author: prpr
* @Date:   2016-07-21 13:38:26
* @Last Modified by:   printempw
* @Last Modified time: 2016-12-10 20:22:13
*/

var gulp     = require('gulp'),
    elixir   = require('laravel-elixir'),
    uglify   = require('gulp-uglify'),
    sass     = require('gulp-sass'),
    cleanCss = require('gulp-clean-css'),
    del      = require('del'),
    zip      = require('gulp-zip');

require('laravel-elixir-replace');

var version  = require('./package.json').version;

var vendor_js = [
    'jquery/dist/jquery.min.js',
    'bootstrap/dist/js/bootstrap.min.js',
    'AdminLTE/dist/js/app.min.js',
    'bootstrap-fileinput/js/fileinput.min.js',
    'bootstrap-fileinput/js/locales/zh.js',
    'iCheck/icheck.min.js',
    'toastr/toastr.min.js',
    'sweetalert2/dist/sweetalert2.min.js',
    'es6-promise/es6-promise.min.js'
];

var vendor_css = [
    'bootstrap/dist/css/bootstrap.min.css',
    'AdminLTE/dist/css/AdminLTE.min.css',
    'bootstrap-fileinput/css/fileinput.min.css',
    'font-awesome/css/font-awesome.min.css',
    'iCheck/skins/square/blue.css',
    'toastr/toastr.min.css',
    'sweetalert2/dist/sweetalert2.min.css'
];

var replacements = [
    ['@import url(https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic);', ''],
    ['../fonts/glyphicons', '../fonts/glyphicons'],
    ['../fonts/fontawesome', '../fonts/fontawesome'],
    ['blue.png', '"../images/blue.png"'],
    ['blue@2x.png', '"../images/blue@2x.png"'],
    ['../img/loading.gif', '"../images/loading.gif"'],
    ['../img/loading-sm.gif', '"../images/loading-sm.gif"']
];

elixir.config.sourcemaps = false;

elixir(function(mix) {
    mix
        .scripts(vendor_js.map(function(js) {
            return 'resources/assets/src/bower_components/' + js;
        }).concat([
            'resources/assets/src/js/utils.js'
        ]), 'resources/assets/dist/js/app.min.js', './')

        .styles(vendor_css.map(function(css) {
            return 'resources/assets/src/bower_components/' + css;
        }), 'resources/assets/dist/css/app.min.css', './')
        .replace('resources/assets/dist/css/app.min.css', replacements)

        // copy fonts & images
        .copy([
            'resources/assets/src/bower_components/bootstrap/dist/fonts/**',
            'resources/assets/src/bower_components/font-awesome/fonts/**'
        ], 'resources/assets/dist/fonts/')
        .copy([
            'resources/assets/src/bower_components/iCheck/skins/square/blue.png',
            'resources/assets/src/bower_components/iCheck/skins/square/blue@2x.png',
            'resources/assets/src/bower_components/bootstrap-fileinput/img/loading.gif',
            'resources/assets/src/bower_components/bootstrap-fileinput/img/loading-sm.gif'
        ], 'resources/assets/dist/images/')

        .task('sass')
        .task('uglify');
});

// compile sass
gulp.task('sass', function () {
    gulp.src('resources/assets/src/sass/*.scss')
        .pipe(sass().on('error', sass.logError))
        .pipe(cleanCss())
        .pipe(gulp.dest('./resources/assets/dist/css'));
});

gulp.task('uglify', function() {
    gulp.src('resources/assets/src/js/*.js')
        .pipe(uglify())
        .pipe(gulp.dest('./resources/assets/dist/js'));
});

// release
gulp.task('zip', function() {
    // delete cache files
    del([
        'storage/logs/*',
        'storage/debugbar/*',
        'storage/yaml-translation/*',
        'storage/framework/cache/*',
        'storage/framework/sessions/*',
        'storage/framework/views/*'
    ]);

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
        .pipe(zip('blessing-skin-server-v'+version+'.zip'))
        .pipe(gulp.dest('../'));
});
