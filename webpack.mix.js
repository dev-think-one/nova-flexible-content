let mix = require('laravel-mix');

mix.webpackConfig(require('./webpack.config.js'));

mix.setPublicPath('dist')
  .js('resources/js/field.js', 'js').vue();

mix.sass('resources/sass/field.scss', 'css');

