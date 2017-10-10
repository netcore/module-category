let mix = require('laravel-mix');
let shell = require('node-exec');

const moduleDir = __dirname;
const resPath = moduleDir + '/Resources/assets';
const compileTo = moduleDir + '/Assets';

mix
    .js(resPath + '/js/categories.js', compileTo + '/admin/js/categories.js')
    .then(() => {
        if(process.env.MIX_PUBLISH_AFTER_COMPILE !== 'true') {
            return true;
        }

        shell.run('php ' + process.env.MIX_PROJECT_PATH + '/artisan module:publish');
    });