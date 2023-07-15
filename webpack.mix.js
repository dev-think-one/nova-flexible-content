let mix = require('laravel-mix')

require('./nova.mix')
const path = require("path");

mix
  .setPublicPath('dist')
  .js('resources/js/field.js', 'js')
  .vue({ version: 3 })
  .sass('resources/sass/field.scss', 'css')
  .alias({
    'nova-mixins': path.join(__dirname,'./vendor/laravel/nova/resources/js/mixins')
  })
  .nova('yaroslawww/nova-flexible-content')
