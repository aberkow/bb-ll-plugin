/**
 *
 * This is the same gulpfile that is included with the gulp-4 dev image.
 * It's included for reference so you can see how it works.
 * To use this file instead of the one in the container, mount it to the container via docker-compose
 *
 */

const gulp = require('gulp')
const browserSync = require('browser-sync')

// use webpack config to make sure babel polyfills work correctly
const webpack = require('webpack-stream')

const del = require('del')

const pluginOpts = {
  DEBUG: true,
  camelize: true,
  lazy: true
}

// select the right config file based on env
const webpackConfig = process.env.NODE_ENV !== 'production' ? require('./webpack.dev') : require('./webpack.prod')

const plugins = require('gulp-load-plugins')(pluginOpts)

const paths = {
  styles: {
    src: 'src/sass/**/*.scss',
    dest: 'build/css/'
  },
  js: {
    src: 'src/js/**/*.js',
    dest: 'build/js/'
  }
}
const onError = (err) => console.log(`Error -> ${err}`);

const clean = () => del(['build'])

/**
 * 
 * Get all styles from src/ and compress them
 * Ensures that CSS grid will be compatible with IE
 * 
 */
const styles = () => {
  return gulp.src(paths.styles.src)
    .pipe(plugins.plumber({ errorHandler: onError }))
    .pipe(plugins.sourcemaps.init())
    .pipe(plugins.sass({
      outputStyle: 'compressed'
    }))
    .pipe(plugins.autoprefixer({
      grid: "autoplace"
    }))
    .pipe(plugins.sourcemaps.write('.'))
    .pipe(gulp.dest(paths.styles.dest))
    .pipe(browserSync.stream())
}

/**
 * 
 * Use webpack to polyfill and handle ES6 -> ES5
 * In production mode also minifies the built files
 * 
 */
const scripts = () => {
  return gulp.src(paths.js.src)
    .pipe(plugins.plumber({ errorHandler: onError }))
    .pipe(plugins.sourcemaps.init())
    .pipe(webpack(webpackConfig))
    .pipe(plugins.sourcemaps.write('.'))
    .pipe(gulp.dest(paths.js.dest))
    .pipe(browserSync.stream())
}

// clean the build directory
gulp.task('clean', gulp.series(clean));

gulp.task('watch', () => {
  const files = [paths.styles.src, paths.js.src]

  browserSync.init(files, {
    proxy: "web:80",
    notify: true,
    open: false
  })

  gulp.watch(
    // watch the following paths
    [paths.styles.src, paths.js.src],
    // when gulp watch is called, make sure the tasks are run
    // if this is set to true (default), the task won't run till a change happens
    // this is no good if the build directory doesn't exist.
    { ignoreInitial: false },
    // run the tasks in parallel
    gulp.parallel(styles, scripts)
  )
})

// default - clean the build directory and then run tasks in parallel
gulp.task('default', gulp.series(clean, gulp.parallel(styles, scripts)))