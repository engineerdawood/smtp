var gulp = require('gulp'),
    concat = require('gulp-concat'),
    uglify = require('gulp-uglify'),
    rename = require('gulp-rename'),
    sass = require('gulp-ruby-sass'),
    autoprefixer = require('gulp-autoprefixer'),
    browserSync = require('browser-sync').create();

var DEST = './css_t/';

var compileSASS = function (filename, options) {
  return sass('./sass/*.scss', options)
        .pipe(autoprefixer('last 2 versions', '> 5%'))
        .pipe(concat(filename))
        .pipe(gulp.dest(DEST))
        .pipe(browserSync.stream());
};

gulp.task('sass', function() {
    return compileSASS('custom.css', {});
});

gulp.task('sass-minify', function() {
    return compileSASS('custom.min.css', {style: 'compressed'});
});

gulp.task('browser-sync', function() {
    return;
    browserSync.init({
        proxy: "vop.app"
    });
});

gulp.task('watch', function() {
  // Watch .html files
  //gulp.watch('production/*.html', browserSync.reload);
  // Watch .scss files
  gulp.watch('./sass/*.scss', ['sass', 'sass-minify']);
});

// Default Task
gulp.task('default', ['browser-sync', 'watch']);