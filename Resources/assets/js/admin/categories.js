'use strict';

import axios from 'axios';
import EventBus from './event-bus';

import CategoriesTree from './components/CategoriesTree';
import CategoriesForm from './components/CategoriesForm';

new Vue({
    /**
     * Element to bind the app.
     */
    el: '#categoryApp',

    /**
     * Define components.
     */
    components: {
        CategoriesTree,
        CategoriesForm
    },

    /**
     * App data.
     */
    data: {
        group: categoryModule.categoryGroup,
        routes: categoryModule.routes,
        languages: _.keyBy(categoryModule.languages, 'iso_code'),
        categories: {},
        isLoading: true
    },

    /**
     * Created event.
     */
    async created() {
        this.setupEventListeners();
        await this.loadCategories();
        this.unblockPanel();
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
        async loadCategories() {
            let {data: categories} = await axios.get(this.routes.fetch);

            this.categories = categories;
            EventBus.$emit('jstree::set-categories', categories);

            return categories;
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
            this.isLoading = true;
        },

        /**
         * Unblock panel and allow editing/ordering.
         */
        unblockPanel() {
            this.isLoading = false;
        },

        /**
         * Reload categories and unblock panel.
         */
        reloadAndUnblock() {
            this.loadCategories().then(() => {
                this.isLoading = false;
            });
        }
    }
});