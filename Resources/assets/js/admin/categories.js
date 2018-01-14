'use strict';

import 'block-ui';
import axios from 'axios';
import EventBus from './event-bus';

new Vue({
    /**
     * Element to bind the app.
     */
    el: '#categoryApp',

    /**
     * Define components.
     */
    components: {
        'categories-tree': require('./components/CategoriesTree.vue'),
        'categories-form': require('./components/CategoriesForm.vue')
    },

    /**
     * App data.
     */
    data: {
        group: categoryModule.categoryGroup,
        routes: categoryModule.routes,
        languages: _.keyBy(categoryModule.languages, 'iso_code'),
        categories: {}
    },

    /**
     * Created event.
     */
    created() {
        this.setupEventListeners();
        this.loadCategories();
    },

    /**
     * Define methods.
     */
    methods: {
        /**
         * Setup event listeners.
         */
        setupEventListeners() {
            EventBus.$on('categories::block-panel', this.blockPanel);
            EventBus.$on('categories::unblock-panel', this.unblockPanel);
            EventBus.$on('categories::reload-and-unblock', this.reloadAndUnblock);
            EventBus.$on('jstree::node-moved', this.updateTreeOrderRequest);
        },

        /**
         * Load categories.
         */
        loadCategories() {
            return new Promise((resolve, reject) => {
                axios.get(this.routes.fetch).then(({data: categories}) => {
                    this.categories = categories;
                    EventBus.$emit('jstree::set-categories', categories);
                    resolve(categories);
                }).catch(reject);
            });
        },

        /**
         * Post tree data to backend.
         *
         * @param data
         */
        updateTreeOrderRequest(data) {
            this.blockPanel();

            axios.post(this.routes.order, data).then(() => {
                this.unblockPanel();
            });
        },

        /**
         * Block panel to disable any manipulations with categories while request in process.
         */
        blockPanel() {
            $(this.$el).block({
                message: 'Processing...',
                css: {
                    border: '1px solid #000',
                    background: 'rgba(0,0,0,0.5)',
                    color: '#fff',
                    padding: '15px'
                }
            });
        },

        /**
         * Unblock panel and allow editing/ordering.
         */
        unblockPanel() {
            $(this.$el).unblock();
        },

        /**
         * Reload categories and unblock panel.
         */
        reloadAndUnblock() {
            this.loadCategories().then(() => {
                this.unblockPanel();
            });
        }
    }
});