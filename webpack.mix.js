let mix = require('laravel-mix')

require('./nova.mix')
const path = require("path");

mix
  .setPublicPath('dist')
  .js('resources/js/field.js', 'js')
  .vue({ version: 3 })
  .sass('resources/sass/field.scss', 'css')
  .alias({
    '@': path.resolve('resources/js'),
    'nova-mixins': path.join(__dirname,'./vendor/laravel/nova/resources/js/mixins')
  })
  .nova('think.studio/nova-flexible-content')
