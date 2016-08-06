/*
* @Author: prpr
* @Date:   2016-07-21 13:38:26
* @Last Modified by:   printempw
* @Last Modified time: 2016-08-06 22:13:01
*/

var gulp     = require('gulp'),
    jshint   = require('gulp-jshint'),
    concat   = require('gulp-concat'),
    uglify   = require('gulp-uglify'),
    sass     = require('gulp-ruby-sass'),
    cleanCss = require('gulp-clean-css'),
    rename   = require('gulp-rename'),
    del      = require('del'),
    replace  = require('gulp-replace')
    zip      = require('gulp-zip');

var version  = require('./package.json').version;

/**
 * Copy files from bower_components to dist for later operations
 */
gulp.task('copy', function(cb) {

    gulp.src('./assets/bower_components/jquery/dist/jquery.min.js')
        .pipe(gulp.dest('./assets/libs/js/'));

    gulp.src('./assets/bower_components/bootstrap/dist/css/bootstrap.min.css')
        .pipe(gulp.dest('./assets/libs/css/'));

    gulp.src('./assets/bower_components/bootstrap/dist/fonts/**')
        .pipe(gulp.dest('./assets/fonts/'));

    gulp.src('./assets/bower_components/bootstrap/dist/js/bootstrap.min.js')
        .pipe(gulp.dest('./assets/libs/js/'));

    gulp.src('./assets/bower_components/AdminLTE/dist/css/AdminLTE.min.css')
        .pipe(replace('@import url(https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic);', ''))
        .pipe(gulp.dest('./assets/libs/css/'));

    gulp.src('./assets/bower_components/AdminLTE/dist/css/skins/**')
        .pipe(gulp.dest('./assets/libs/skins/'));

    gulp.src('./assets/bower_components/AdminLTE/dist/js/app.min.js')
        .pipe(gulp.dest('./assets/libs/js/'));

    gulp.src('./assets/bower_components/bootstrap-fileinput/css/fileinput.min.css')
        .pipe(gulp.dest('./assets/libs/css/'));

    gulp.src(['./assets/bower_components/bootstrap-fileinput/js/fileinput.min.js',
              './assets/bower_components/bootstrap-fileinput/js/locales/zh.js'])
        .pipe(concat('fileinput.min.js'))
        .pipe(gulp.dest('./assets/libs/js/'));

    gulp.src('./assets/bower_components/font-awesome/css/font-awesome.min.css')
        .pipe(gulp.dest('./assets/libs/css/'));

    gulp.src('./assets/bower_components/font-awesome/fonts/**')
        .pipe(gulp.dest('./assets/fonts/'));

    gulp.src('./assets/bower_components/iCheck/skins/square/blue.css')
        .pipe(replace('blue.png', '"../images/blue.png"'))
        .pipe(replace('blue@2x.png', '"../images/blue@2x.png"'))
        .pipe(gulp.dest('./assets/libs/css/'));

    gulp.src('./assets/bower_components/iCheck/skins/square/blue.png')
        .pipe(gulp.dest('./assets/images/'));

    gulp.src('./assets/bower_components/iCheck/skins/square/blue@2x.png')
        .pipe(gulp.dest('./assets/images/'));

    gulp.src('./assets/bower_components/iCheck/icheck.min.js')
        .pipe(gulp.dest('./assets/libs/js/'));

    gulp.src('./assets/bower_components/toastr/toastr.min.css')
        .pipe(gulp.dest('./assets/libs/css/'));

    gulp.src('./assets/bower_components/toastr/toastr.min.js')
        .pipe(gulp.dest('./assets/libs/js/'));
});

gulp.task('lint', function() {
    return gulp.src('./assets/js/*.js')
    .pipe(jshint())
    .pipe(jshint.reporter('default'));
});

gulp.task('lib-css', function() {
    gulp.src([
            './assets/libs/css/bootstrap.min.css',
            './assets/libs/css/AdminLTE.min.css',
            './assets/libs/css/fileinput.min.css',
            './assets/libs/css/font-awesome.min.css',
            './assets/libs/css/blue.css',
            './assets/libs/css/toastr.min.css'
        ])
        .pipe(concat('app.min.css'))
        .pipe(cleanCss())
        .pipe(gulp.dest('./assets/dist/'))
});

gulp.task('lib-scripts', function() {
    gulp.src([
            './assets/libs/js/jquery.min.js',
            './assets/libs/js/bootstrap.min.js',
            './assets/libs/js/app.min.js',
            './assets/libs/js/icheck.min.js',
            './assets/libs/js/fileinput.min.js',
            './assets/libs/js/toastr.min.js',
            './assets/src/js/utils.js'
        ])
        .pipe(concat('app.min.js'))
        .pipe(uglify())
        .pipe(gulp.dest('./assets/dist/'))
});

/**
 * Concat css and js files to one file
 */
gulp.task('concat', ['lib-css', 'lib-scripts']);

// compile sass
gulp.task('sass', function() {
    return sass('./assets/src/sass/*.scss')
                .on('error', function (error) {
                    console.error('Error!', error.message);
                })
                .pipe(cleanCss())
                // .pipe(rename({'suffix': '.min'}))
                .pipe(gulp.dest('./assets/dist/css'));
})

gulp.task('scripts', function() {
    gulp.src([
            './assets/src/js/*.js',
            '!./assets/src/js/utils.js'
        ])
        .pipe(uglify())
        // .pipe(rename({'suffix': '.min'}))
        .pipe(gulp.dest('./assets/dist/js'));

});

gulp.task('minify', ['sass', 'scripts']);

gulp.task('clean', ['concat', 'minify'], function (cb) {
    return del([
        './assets/libs/css/**',
        './assets/libs/js/**'
    ], cb);
});

gulp.task('build', ['concat', 'minify', 'clean']);

// release
gulp.task('zip', function() {
    del('resources/cache/*');

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
        .pipe(zip('blessing-skin-server-'+version+'.zip'))
        .pipe(gulp.dest('./'));
});

gulp.task('default', ['copy']);
