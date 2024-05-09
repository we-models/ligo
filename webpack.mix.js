const mix = require('laravel-mix');

require('laravel-vue-i18n/mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
    .js('resources/js/sb-admin.js', 'public/js')
    .js('node_modules/quill/dist/quill.min.js', 'public/js')
    .js('node_modules/quill/dist/quill.core.js', 'public/js')
    .js('node_modules/quill-image-resize/src/ImageResize.js', 'public/js')
    .vue().i18n()
    .sass('resources/sass/app.scss', 'public/css')
    .css('resources/css/sb-admin.css', 'public/css')
    .css('resources/css/panel.css', 'public/css')
    .css('resources/css/guest.css', 'public/css')
    .css('node_modules/quill/dist/quill.bubble.css', 'public/css')
    .css('node_modules/quill/dist/quill.snow.css', 'public/css')
    .css('node_modules/quill/dist/quill.core.css', 'public/css')
    .copy(
        'node_modules/@fortawesome/fontawesome-free/webfonts',
        'public/webfonts'
    ).copy('resources/js/firebase-messaging-sw.js', 'public')
    .copyDirectory('resources/imgs', 'public/images')
    .copyDirectory('resources/sounds', 'public/sounds');


