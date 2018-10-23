'use strict';

var gulp = require( 'gulp' );
var sass = require( 'gulp-sass' );

sass.compiler = require( 'node-sass' );

gulp.task( 'sass', function () {
	gulp.src( './public/sass/*.scss' )
	    .pipe( sass( { outputStyle: 'compressed' } ).on( 'error', sass.logError ) )
	    .pipe( gulp.dest( './public/css' ) );
	gulp.src( './admin/sass/*.scss' )
	    .pipe( sass( { outputStyle: 'compressed' } ).on( 'error', sass.logError ) )
	    .pipe( gulp.dest( './admin/css' ) );
} );

gulp.task( 'sass:watch', function () {
	gulp.watch( [ './public/sass/*.scss', './admin/sass/*.scss' ], [ 'sass' ] );
} );