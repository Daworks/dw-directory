'use strict';

const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));

// Compile SCSS to CSS
function compileSass() {
    return gulp.src(['./public/scss/*.scss', './admin/scss/*.scss'], { base: '.', allowEmpty: true })
        .pipe(sass({ outputStyle: 'compressed' }).on('error', sass.logError))
        .pipe(gulp.dest(function(file) {
            // Output to css folder in same directory as source
            return file.base.replace('/scss', '/css');
        }));
}

// Alternative: Compile public and admin separately for clearer output paths
function compilePublicSass() {
    return gulp.src('./public/scss/*.scss', { allowEmpty: true })
        .pipe(sass({ outputStyle: 'compressed' }).on('error', sass.logError))
        .pipe(gulp.dest('./public/css'));
}

function compileAdminSass() {
    return gulp.src('./admin/scss/*.scss', { allowEmpty: true })
        .pipe(sass({ outputStyle: 'compressed' }).on('error', sass.logError))
        .pipe(gulp.dest('./admin/css'));
}

// Watch for changes
function watchSass() {
    gulp.watch('./public/scss/*.scss', compilePublicSass);
    gulp.watch('./admin/scss/*.scss', compileAdminSass);
}

// Export tasks
exports.sass = gulp.parallel(compilePublicSass, compileAdminSass);
exports['sass:watch'] = gulp.series(exports.sass, watchSass);
exports.default = exports.sass;
