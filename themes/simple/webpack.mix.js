const mix = require('laravel-mix');

mix.setResourceRoot('../');
mix.js('javascript/components/*.js', 'dist/main.js');
mix.sass('scss/main.scss', 'dist/main.css');

mix.webpackConfig({
});
