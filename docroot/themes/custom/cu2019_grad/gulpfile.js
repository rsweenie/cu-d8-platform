var gulp = require('gulp'),

    debug = require('gulp-debug'),
    plumber = require('gulp-plumber'),
    sass = require('gulp-sass'),
    sourcemaps = require('gulp-sourcemaps'),
    csscomb = require('gulp-csscomb'),
    cmq = require('gulp-combine-mq'),
    please = require('gulp-pleeease'),
    watch = require('gulp-watch'),
    notify = require('gulp-notify'),
    concat = require('gulp-concat'),
    buffer = require('vinyl-buffer'),
    browserify = require('browserify'),
    runSequence = require('run-sequence'),
    gutil = require('gulp-util'),
    source = require('vinyl-source-stream'),
    iconfont = require('gulp-iconfont'),
    iconfontCss = require('gulp-iconfont-css'),
    eslint = require('gulp-eslint');

    autoprefixer = require('gulp-autoprefixer'),
    gcmq = require('gulp-group-css-media-queries'),
    pxtorem = require('gulp-pxtorem');

var config = {
  production: !!gutil.env.production
};

console.log("Production Environment: "+config.production);
// Paths
var sass_build = 'src/sass/',
    js_build = 'src/js/',
    icon_build = 'src/icons/',
    css_output = 'assets/css/',
    js_output = 'assets/js/';

// Fontname
var fontName = 'cu_icons';

var prefixes = { "browsers":[
  "last 3 versions",
  "ie 9",
  "ie 10",
  "ios 6",
  "ios 7",
  "ios 8",
  "ios 9"
]}
// Settings
var plumberOptions = {
  errorHandler: notify.onError("Error: <%= error.message %>")
};

var pleeeaseDev = {
    "autoprefixer": prefixes,
    "filters": true,
    "rem": false,
    "psuedoElements": true,
    "opacity": false,
    "rebaseUrls": false,
    "import": true,
    "minifier": false,
    "mqpacker": false,
    "next": false
},
pleeeaseProd = {
  "autoprefixer": prefixes,
  "filters": true,
  "rem": false,
  "psuedoElements": true,
  "opacity": false,
  "rebaseUrls": false,
  "import": true,
  "minifier": true,
  "mqpacker": false,
  "next": false
};

// gulp.task('sass', function () {
//   gulp.src(sass_build+'/**/*.scss')
//   .pipe(plumber(plumberOptions))
//   .pipe(config.production ? gutil.noop() : sourcemaps.init())
//   .pipe(sass({
//       outputStyle: 'expanded'
//     }))
//   // .on("error", notify.onError(function (error) {
//   //       return error.message;
//   // }))
//   // .pipe(cmq({
//   //   beautify: true
//   // }))
//   // .pipe(config.production ? please(pleeeaseProd) : please(pleeeaseDev))
//   // .pipe(config.production ? gutil.noop() : sourcemaps.write())
//   .pipe(gulp.dest(css_output))
// });
// TEMP SASS
var sassOptions = {
  errLogToConsole: true,
  outputStyle: 'compact',
  sourceComments: true
};

var autoprefixerOptions = {
  browsers: ['last 2 versions']
};

var pxtoremOptions = {
  propList: [
    'font',
    'font-size',
    'line-height',
    'padding',
    'padding-top',
    'padding-left',
    'padding-right',
    'padding-bottom',
    'margin',
    'margin-top',
    'margin-left',
    'margin-right',
    'margin-bottom',
    'width',
    'height',
    'border',
    'border-radius',
    'border-top-left-radius',
    'border-top-right-radius',
    'border-bottom-left-radius',
    'border-bottom-right-radius',
    'top',
    'left',
    'bottom',
    'right'
  ],
  rootValue: 10
};

var eslintRules = {
  "consistent-return": ["off"],
  "no-underscore-dangle": ["off"],
  "max-nested-callbacks": ["warn", 3],
  "no-plusplus": ["warn", {
    "allowForLoopAfterthoughts": true
  }],
  "no-param-reassign": ["off"],
  "no-prototype-builtins": ["off"],
  "valid-jsdoc": ["warn", {
    "prefer": {
      "returns": "return",
      "property": "prop"
    },
    "requireReturn": false
  }],
  //"no-unused-vars": ["warn"],
  "operator-linebreak": ["error", "after", { "overrides": { "?": "ignore", ":": "ignore" } }]
};

var eslintGlobals = [
  "jQuery",
  "$"
];

gulp.task('sass', function(){
  return gulp
    .src(sass_build+'/**/*.scss')
    .pipe(sourcemaps.init())
    .pipe(sass(sassOptions).on('error', sass.logError))
    .pipe(gcmq())
    //.pipe(sourcemaps.write())
    .pipe(autoprefixer(autoprefixerOptions))
    .pipe(pxtorem(pxtoremOptions))
    .pipe(gulp.dest(css_output))
});


gulp.task('scripts', function () {
    browserify({
      entries: js_build+'_custom/scripts.js',
      debug: true
    })
    .bundle()
    .on('error', notify.onError(function (err) {
     return "Browserify Error: "+err.message;
    }))
    .pipe(source('scripts.js'))
    .pipe(buffer())
    .pipe(config.production ? gutil.noop() : sourcemaps.init({loadMaps: true}))
    .pipe(config.production ? uglify() : gutil.noop())
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest(js_output));
});

gulp.task('js-plugins', function() {
  gulp.src(js_build+'/_lib/*.js')
    .pipe(concat('plugins.js'))
    .pipe(uglify())
    .pipe(gulp.dest(js_output))
});

gulp.task('iconfont', function(){
  gulp.src(['./src/icons/*.svg'], {base: './src'})
    .pipe(iconfontCss({
      fontName: fontName,
      targetPath: '../../../src/sass/base/fonts/_icons.scss',
      fontPath: '../fonts/icons/'
    }))
    .pipe(iconfont({
      fontName: fontName,
      formats: ['ttf', 'eot', 'woff', 'woff2', 'svg'],
      normalize:true,
      fontHeight: 1001
     }))
    .pipe(gulp.dest('assets/fonts/icons/'));
});

gulp.task('js', function() {
  gulp.src(js_build+'/**/*.js')
    .pipe(sourcemaps.init())
    .pipe(debug({title: 'js:'}))
    .pipe(eslint({
      rules: eslintRules,
      globals: eslintGlobals,
      useEslintrc: false
    }))
    .pipe(eslint.format())
    .pipe(eslint.failOnError())
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest(js_output));
});

gulp.task('js:watch', ['js'], function() {
  gulp.watch([js_build+'**/*.js'], ['js']);
});

gulp.task('watch', function() {
  gulp.watch(sass_build+'**/*.scss', function(){ runSequence('sass')});
  gulp.watch(icon_build+'*', function(){ runSequence('iconfont', 'sass')});
});

gulp.task('stage', ['iconfont', 'sass']);
gulp.task('FED',['watch']);
