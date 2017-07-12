'use strict';

var gulp        = require('gulp'),
    babel       = require('gulp-babel'),
    uglify      = require('gulp-uglify'),
    sass        = require('gulp-sass'),
    cleanCss    = require('gulp-clean-css'),
    del         = require('del'),
    concat      = require('gulp-concat'),
    zip         = require('gulp-zip'),
    replace     = require('gulp-batch-replace'),
    notify      = require('gulp-notify'),
    runSequence = require('run-sequence');

var version  = require('./package.json').version;

var srcPath  = 'resources/assets/src';
var distPath = 'resources/assets/dist';

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
    'resources/assets/dist/js/general.js',
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

var styleReplacements = [
    ['blue.png', '"../images/blue.png"'],
    ['blue@2x.png', '"../images/blue@2x.png"'],
    ['../img/loading.gif', '"../images/loading.gif"'],
    ['../img/loading-sm.gif', '"../images/loading-sm.gif"'],
    [/@import url\((.*)italic\);/g, ''],
];

var scriptReplacements = [
    ['$.AdminLTE.layout.activate(),', '']
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

// aka. `yarn run build`
gulp.task('default', ['build']);

// Build the things!
gulp.task('build', function (callback) {
    runSequence('clean', ['compile-es6', 'compile-sass'], 'publish-vendor', callback);
});

// Concentrate all vendor scripts & styles to one dist file
gulp.task('publish-vendor', ['compile-es6'], callback => {
    // JavaScript files
    gulp.src(convertNpmRelativePath(vendorScripts))
        .pipe(concat('app.js'))
        .pipe(replace(scriptReplacements))
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
    gulp.src('node_modules/admin-lte/dist/css/skins/**')
        .pipe(gulp.dest(`${distPath}/css/skins/`));
    // 3D skin preview
    gulp.src(['skin-preview/**', 'Chart.min.js'].map(path => `${srcPath}/vendor/${path}`))
        .pipe(gulp.dest(`${distPath}/js/`));

    callback();
});

// Compile sass to css
gulp.task('compile-sass', () => {
    return gulp.src(`${srcPath}/sass/*.scss`)
        .pipe(sass().on('error', sass.logError))
        .pipe(cleanCss())
        .pipe(gulp.dest(`${distPath}/css`));
});

// Compile ES6 scripts to ES5
gulp.task('compile-es6', () => {
    return gulp.src(`${srcPath}/js/*.js`)
        .pipe(babel({ presets: ['es2015'] }))
        .pipe(uglify())
        .pipe(gulp.dest(`${distPath}/js`));
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
    gulp.watch(`${srcPath}/sass/*.scss`, ['compile-sass'], () => notify({ message: 'Sass files compiled!' }));
    // watch .js files
    gulp.watch(`${srcPath}/js/*.js`, ['compile-es6'], () => notify({ message: 'ES6 scripts compiled!' }));
    gulp.watch(`${srcPath}/js/general.js`, ['build']);
});

function convertNpmRelativePath(paths) {
    return paths.map(relativePath => {
        return relativePath.startsWith('resources') ? relativePath : `node_modules/${relativePath}`;
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
    return del([`${distPath}/**/*`]);
}
