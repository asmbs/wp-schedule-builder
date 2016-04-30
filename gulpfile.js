var gulp = require('gulp');

// Load modules
var autoprefixer = require('gulp-autoprefixer');
var concat       = require('gulp-concat');
var cssnano      = require('gulp-cssnano');
var del          = require('del');
var gutil        = require('gulp-util');
var imagemin     = require('gulp-imagemin');
var lazypipe     = require('lazypipe');
var less         = require('gulp-less');
var livereload   = require('gulp-livereload');
var rename       = require('gulp-rename');
var runSequence  = require('run-sequence');
var uglify       = require('gulp-uglify');

// Load manifest
var manifest = require('asset-builder')('assets/manifest.json');
var paths     = manifest.paths;
var config    = manifest.config || {};

// Pre-wire tasks ------------------------------------------------------

var scriptTasks = function(file) {
    var dest = paths.dist + 'scripts';

    return lazypipe()
        .pipe(concat, file)
        .pipe(gulp.dest, dest)
        .pipe(uglify)
        .pipe(rename, {suffix: '.min'})
        .pipe(gulp.dest, dest)
        .pipe(livereload)
        ();
};

var styleTasks = function(file) {
    var dest = paths.dist + 'styles';

    return lazypipe()
        .pipe(less)
        .pipe(concat, file)
        .pipe(autoprefixer, {browsers: ['last 2 versions']})
        .pipe(gulp.dest, dest)
        .pipe(cssnano, {safe: true})
        .pipe(rename, {suffix: '.min'})
        .pipe(gulp.dest, dest)
        .pipe(livereload)
        ();
};

var imageTasks = function() {
    var dest = paths.dist + 'images';

    return lazypipe()
        .pipe(imagemin, {
            progressive: true,
            interlaced: true,
            svgoPlugins: [
                {removeUnknownsandDefaults: false},
                {cleanupIDs: false}
            ]
        })
        .pipe(gulp.dest, dest)
        .pipe(livereload)
        ();
};

// Register tasks ------------------------------------------------------

// Clean dist directory
gulp.task('clean', del.bind(null, [paths.dist]));

// Run script tasks
gulp.task('scripts', function() {
    manifest.forEachDependency('js', function(d) {
        gulp.src(d.globs).pipe(scriptTasks(d.name));
        gutil.log('Created script', gutil.colors.yellow(d.name));
    });
});

// Run style tasks
gulp.task('styles', function() {
    manifest.forEachDependency('css', function(d) {
        gulp.src(d.globs).pipe(styleTasks(d.name));
        gutil.log('Created stylesheet', gutil.colors.yellow(d.name));
    });
});

// Copy and compress images
gulp.task('images', function() {
    return gulp.src(manifest.globs.images)
        .pipe(imageTasks());
});

// Copy fonts
gulp.task('fonts', function() {
    return gulp.src(manifest.globs.fonts)
        .pipe(gulp.dest(paths.dist + 'fonts'))
        .pipe(livereload({refresh: true}));
});

gulp.task('build', function(callback) {
    runSequence('clean', 'scripts', 'styles', 'images', 'fonts', callback);
});

livereload.options = {port: 35729};

// Watcher
gulp.task('watch', function() {
    livereload.listen();
    // Watch stylesheets
    gulp.watch(paths.source + 'styles/**/*', function(e) {
        gutil.log('File', e.path, e.type, '- running style tasks');
        runSequence('styles');
    });
    // Watch scripts
    gulp.watch(paths.source + 'scripts/**/*', function(e) {
        gutil.log('File', e.path, e.type, '- running script tasks');
        runSequence('scripts');
    });
    // Watch images
    gulp.watch(paths.source + 'images/**/*', function(e) {
        gutil.log('File', e.path, e.type, '- running image tasks');
        runSequence('images');
    });
    // Watch fonts
    gulp.watch(paths.source + 'fonts/**/*', function(e) {
        gutil.log('Change detected in fonts directory - running font tasks');
        runSequence('fonts');
    });
    // Watch asset configs
    gulp.watch(['bower.json', 'assets/manifest.json'], function(e) {
        gutil.log('Asset config changed - rebuilding');
        runSequence('build');
    });
    // Watch theme templates and code library
    gulp.watch(['*.php', 'templates/*.php', 'lib/**/*.php'], function(e) {
        gutil.log('Theme template or source file changed - reloading');
        gulp.src(e.path)
            .pipe(livereload());
    });
});

// Default task
gulp.task('default', function() {
    console.log('Nothing to do here');
    console.log(manifest.globs.js);
});
