'use strict';

require('jstree');

new Vue({
    el: '#categoryApp',
    components: {
        'categories-tree': require('./components/CategoriesTree.vue')
    }
});