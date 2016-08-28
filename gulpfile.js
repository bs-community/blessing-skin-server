/*
* @Author: prpr
* @Date:   2016-07-21 13:38:26
* @Last Modified by:   printempw
* @Last Modified time: 2016-08-28 11:44:15
*/

var gulp     = require('gulp'),
    elixir   = require('laravel-elixir'),
    uglify   = require('gulp-uglify'),
    sass     = require('gulp-sass'),
    cleanCss = require('gulp-clean-css'),
    zip      = require('gulp-zip');

require('laravel-elixir-replace');

var version  = require('./package.json').version;

var vendor_js = [
    'resources/assets/bower_components/jquery/dist/jquery.min.js',
    'resources/assets/bower_components/bootstrap/dist/js/bootstrap.min.js',
    'resources/assets/bower_components/AdminLTE/dist/js/app.min.js',
    'resources/assets/bower_components/bootstrap-fileinput/js/fileinput.min.js',
    'resources/assets/bower_components/bootstrap-fileinput/js/locales/zh.js',
    'resources/assets/bower_components/iCheck/icheck.min.js',
    'resources/assets/bower_components/toastr/toastr.min.js',
    'resources/assets/bower_components/sweetalert2/dist/sweetalert2.min.js',
    'resources/assets/bower_components/es6-promise/es6-promise.min.js'
];

var vendor_css = [
    'resources/assets/bower_components/bootstrap/dist/css/bootstrap.min.css',
    'resources/assets/bower_components/AdminLTE/dist/css/AdminLTE.min.css',
    'resources/assets/bower_components/bootstrap-fileinput/css/fileinput.min.css',
    'resources/assets/bower_components/font-awesome/css/font-awesome.min.css',
    'resources/assets/bower_components/iCheck/skins/square/blue.css',
    'resources/assets/bower_components/toastr/toastr.min.css',
    'resources/assets/bower_components/sweetalert2/dist/sweetalert2.min.css'
];

var replacements = [
    ['@import url(https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic);', ''],
    ['blue.png', '"../images/blue.png"'],
    ['blue@2x.png', '"../images/blue@2x.png"']
];

elixir(function(mix) {
    mix
        .scripts(vendor_js.concat(
            'resources/assets/src/js/utils.js'
        ), 'assets/js/app.min.js', './')

        .styles(vendor_css, 'assets/css/app.min.css', './')

        .replace('assets/css/app.min.css', replacements)

        .copy([
            'resources/assets/bower_components/bootstrap/dist/fonts/**',
            'resources/assets/bower_components/font-awesome/fonts/**'
        ], 'assets/fonts/')

        .copy([
            'resources/assets/bower_components/iCheck/skins/square/blue.png',
            'resources/assets/bower_components/iCheck/skins/square/blue@2x.png'
        ], 'assets/images/')

        .task('sass')
        .task('scripts')
});

// compile sass
gulp.task('sass', function () {
    gulp.src('resources/assets/sass/*.scss')
        .pipe(sass().on('error', sass.logError))
        .pipe(cleanCss())
        .pipe(gulp.dest('./assets/css'));
});

gulp.task('scripts', function() {
    gulp.src('resources/assets/js/*.js')
        .pipe(uglify())
        .pipe(gulp.dest('./assets/js'));
});

// release
gulp.task('zip', function() {
    return gulp.src([
            '**/*.*',
            'LICENSE',
            '!tests/**/*.*',
            '!node_modules/**/*.*',
            '!textures/**/*.*',
            '!.env',
            '!.bowerrc',
            '!.gitignore',
            '!.git/**/*.*',
            '!.git/',
            '!koala-config.json',
            '!gulpfile.js',
            '!package.json',
            '!composer.json',
            '!composer.lock',
            '!bower.json',
            '!assets/bower_components/**/*.*',
            '!assets/src/**/*.*',
            '!.sass-cache/**/*.*',
            '!.sass-cache/'
        ], { dot: true })
        .pipe(zip('blessing-skin-server-v'+version+'.zip'))
        .pipe(gulp.dest('../'));
});
