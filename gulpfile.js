var gulp = require('gulp');
var uglify = require('gulp-uglify');
var livereload = require('gulp-livereload');
var concat = require('gulp-concat');
var del = require('del');
var watch = require('gulp-watch');
var browserify = require('browserify');
var gutil = require('gulp-util');
var runSequence = require('run-sequence');
var autoprefixer = require('gulp-autoprefixer');
var minifycss = require('gulp-minify-css');
var notify = require("gulp-notify");

var p = require('./package.json');

var paths = {
	scripts: [p.devDirectory+'/js/**/*.js'],
	styles: [p.devDirectory+'/css/**/*.css']
};
 
gulp.task('styles', function() {
	return gulp.src(
			[
				'./node_modules/bootstrap/dist/css/bootstrap.min.css',
				'./node_modules/font-awesome/css/font-awesome.min.css',
				p.devDirectory+'/css/**/*.css'
			]
		)
		.pipe(autoprefixer({
			browsers: ['last 2 versions', 'safari 5', 'ie 8', 'ie 9', 'opera 12.1', 'ios 6', 'android 4'],
			cascade: false
		}))
    	.pipe(concat('style.min.css'))
    	// .pipe(minifycss())
    	.pipe(gulp.dest(p.buildDirectory+'/css/'))
    	.on('error', notify.onError({
    			message: 'Error: <%= error.message %>',
    			title: 'Style Error'
    	}))
    	.pipe(notify({
    		message: 'Style Complete',
    		title: 'Complete'
    	}));
});

gulp.task('scripts', function() {
	return gulp.src(
			[
				'./node_modules/jquery/dist/jquery.min.js',
				'./node_modules/bootstrap/dist/js/bootstrap.min.js',
				p.devDirectory+'/js/**/*.js'
			]
		)
    .pipe(concat('app.min.js'))
	// .pipe(uglify())
	.pipe(gulp.dest(p.buildDirectory+'/js'))
	.on('error', notify.onError({
		message: 'Error: <%= error.message %>',
		title: 'JS Error'
	}))
	.pipe(notify({
		message: 'JS Complete',
		title: 'Complete'
	}));
});

gulp.task('fonts', function() {
	return gulp.src(
			[
				'./node_modules/bootstrap/dist/fonts/**',
				'./node_modules/font-awesome/fonts/**',
				p.devDirectory+'/fonts/**'
			]
		)
		.pipe(gulp.dest(p.buildDirectory+'/fonts/'))
		.on('error', notify.onError({
			message: 'Error: <%= error.message %>',
			title: 'Fonts Error'
    	}));
});

gulp.task('images', function() {
	return gulp.src([p.devDirectory+'/img/**'])
		.pipe(gulp.dest(p.buildDirectory+'/img/'))
		.on('error', notify.onError({
			message: 'Error: <%= error.message %>',
			title: 'Images Error'
    	}));
});

gulp.task('clean', function(cb) {
	return del(['./build'], cb);
});

gulp.task('watch', function() {
	watch(paths.scripts, function() {
		runSequence('scripts');
	});

	watch(paths.styles, function() {
		runSequence('styles');
	});

	watch([p.devDirectory+'/fonts/*'], function() {
		runSequence('fonts');
	});

	watch([p.devDirectory+'/img/*'], function() {
		runSequence('images');
	});

	watch([p.buildDirectory+'/**']).on('change', livereload.changed);
	return livereload.listen();
});

gulp.task('default', function(cb) {
	return runSequence('clean','fonts', 'images',['styles','scripts'], 'watch', cb);
});

gulp.task('build', function(cb) {
	return runSequence('clean','fonts', 'images',['styles','scripts'], cb);
});