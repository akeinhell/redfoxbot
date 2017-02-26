gulp = require('gulp')
concat = require('gulp-concat')
filter = require('gulp-filter')
order = require('gulp-order')
notify = require('gulp-notify')
mainBowerFiles = require('main-bower-files')
uglify = require('gulp-uglify')
less = require('gulp-less')
watch = require('gulp-watch')
debug = require('gulp-debug')
coffee = require('gulp-coffee')
sass = require('gulp-sass')
merge = require('merge-stream')

settings = {
  debug: false
  dir:
    sass: 'resources/assets/sass/**/*'
    less: 'resources/assets/less/**/*'
    coffee: 'resources/assets/coffee/**/*.coffee'
  out:
    css: './public/css/'
    fonts: './public/fonts/'
    js: './public/js/'
}

lessFiles = ->
  gulp.src(mainBowerFiles().concat([settings.dir.less]))
  .pipe(filter('**/*.less'))
  .pipe(debug({title: 'less:'}))
  .pipe(less())
  .pipe(concat('less.css'))

sassFiles = ->
  gulp.src(mainBowerFiles().concat([settings.dir.sass]))
  .pipe(filter('**/*.scss'))
  .pipe(debug({title: 'sass:'}))
  .pipe(sass())
  .pipe(concat('sass.css'))

bowerJsFiles = ->
  vendors = ['bower_components/jquery/dist/jquery.min.js',]
  .concat(mainBowerFiles())
  .concat([
    'resources/assets/js/**/*.js'
  ]);

  return gulp.src(vendors)
  .pipe(filter('**.js'))
  .pipe(debug({title: 'js:'}))
  .pipe(concat('bower.js'))


gulp.task 'fonts', ->
  gulp.src(['bower_components/font-awesome/fonts/**'])
  .pipe(debug({title: 'fonts:'}))
  .pipe(gulp.dest(settings.out.fonts))

gulp.task 'js', ->
  bowerJsFiles()
  .pipe(concat('vendor.js'))
  .pipe(uglify())
  .pipe(gulp.dest(settings.out.js));


gulp.task 'css', ->
  merge(sassFiles(), lessFiles())
  .pipe(concat('main.css'))
#  .pipe(minify())
  .pipe(gulp.dest(settings.out.css));


gulp.task 'default', ['build']

gulp.task 'watch', ['build'], ->
#  gulp.watch(settings.dir.less, ['css']);
  gulp.watch(settings.dir.sass, ['css']);
  gulp.watch(settings.dir.coffee, ['js']);

gulp.task('build', ['js', 'css', 'fonts'])

gulp.task 'dev', ['watch']
