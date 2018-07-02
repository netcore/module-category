let mix = require('laravel-mix');

const moduleDir = __dirname;
const resPath = moduleDir + '/Resources/assets';
const compileTo = moduleDir + '/Assets';

mix
    .copy(moduleDir + '/node_modules/jstree/dist/jstree.js', compileTo + '/admin/js/jstree.js')
    .copy(moduleDir + '/node_modules/jstree/dist/themes', compileTo + '/admin/css/jstree-themes')
    .js(resPath + '/js/admin/categories.js', compileTo + '/admin/js/categories.js')
    .disableNotifications();