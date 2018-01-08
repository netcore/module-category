'use strict';

/**
 * --------------------------------------------------------------------------------------------
 * Dependencies
 * --------------------------------------------------------------------------------------------
 */
import _ from 'lodash';
import axios from 'axios';

require('block-ui');

/**
 * --------------------------------------------------------------------------------------------
 * Helpers
 * --------------------------------------------------------------------------------------------
 */
const __mockForm = function (category) {
    let output = {
        icon: _.get(category, 'icon', ''),
        translations: {}
    };

    if (category && category.id) {
        output.id = category.id;
    }

    let languages = window.categoryModule.languages;
    let translations = _.keyBy(_.get(category, 'translations', []), 'locale');

    _.each(languages, (language) => {
        let iso = language.iso_code;

        output.translations[iso] = {
            name: _.get(translations, iso + '.name', ''),
            slug: _.get(translations, iso + '.slug', '')
        };
    });

    return output;
};

/**
 * --------------------------------------------------------------------------------------------
 * Vue App
 * --------------------------------------------------------------------------------------------
 */
new Vue({
    el: '#categoryApp',

    data: {
        selectedNode: null,
        categoryForm: __mockForm(),
        categoryFormAction: 'create',

        languages: {},
        categories: {}
    },

    computed: {
        selectedNodeName() {
            return this.selectedNode ? this.selectedNode.translations[0].name : '';
        },

        showSelectedParentCategory() {
            return this.selectedNode && this.categoryFormAction === 'create';
        },

        showIconSelect() {
            const isEnabled = window.categoryModule.icons.enabled;
            const rootOnly = window.categoryModule.icons.root_only;
            const action = this.categoryFormAction;

            if (!isEnabled) {
                return false;
            }

            // Create as root form
            if (rootOnly && action === 'create' && this.selectedNode) {
                return false;
            }

            // Root edit
            if (rootOnly && action === 'edit' && this.selectedNode.parent !== '#') {
                return false;
            }

            return true;
        }
    },

    created() {
        this.languages = _.keyBy(window.categoryModule.languages, 'iso_code');
        this.__categoryApp__loadCategories();

        let self = this;

        // Setup event listeners
        this.$on('jsTree.nodeSelected', (node) => {
            let selectedNode = self.categories[node.id];

            self.selectedNode = selectedNode;
            self.categoryForm = __mockForm(selectedNode);
            self.categoryFormAction = 'edit';
        });

        this.$on('jsTree.orderChanged', data => {
            this.__categoryApp__blockPanel();

            axios
                .post(window.categoryModule.routes.order, data)
                .then(() => {
                    this.__categoryApp__unblockPanel();
                })
                .catch(() => {
                    $.growl.error({
                        message: 'Unable to save order - server error!'
                    });
                });
        });
    },

    methods: {
        __categoryApp__loadCategories() {
            let self = this;
            this.__categoryApp__blockPanel();

            axios
                .get(window.categoryModule.routes.index)
                .then(res => {
                    self.categories = _.keyBy(res.data, 'id');
                    self.$emit('jsTree.categoriesLoaded', res.data);
                })
                .catch(() => {
                    alert('Unable to load categories - server error!');
                })
                .then(() => {
                    self.__categoryApp__unblockPanel();
                });
        },

        /**
         * Cancel editing/creating
         */
        __categoryApp__cancelCategoryEditing() {
            this.categoryForm = __mockForm();
            this.$emit('jsTree.deselectAllNodes');
            this.categoryFormAction = 'create';
            this.selectedNode = null;
        },

        /**
         * Create category with parent
         */
        __categoryApp__addChildToCategory() {
            this.categoryFormAction = 'create';
            this.categoryForm = __mockForm();
        },

        /**
         * Save category
         */
        __categoryApp__saveCategory() {
            this.__categoryApp__blockPanel();

            let route, method;

            if (this.categoryFormAction === 'edit') {
                route = window.categoryModule.routes.update.replace('--ID--', this.selectedNode.id);
                method = 'PUT';
            } else {
                route = window.categoryModule.routes.index;
                method = 'POST';
            }

            let data = _.merge({_method: method}, this.categoryForm);
            let self = this;

            if (this.selectedNode) {
                data = _.merge(data, {parent: this.selectedNode.id});
            }

            axios
                .post(route, data)
                .then(() => {
                    $.growl.success({message: 'Category saved!'});
                    self.__categoryApp__loadCategories();
                })
                .catch(err => {
                    if (err.response.status !== 422) {
                        $.growl.error({message: 'Unknown server error!'});
                    } else {
                        let errors = err.response.data.errors;
                        let error = errors[Object.keys(errors)[0]];

                        if (typeof error !== 'string') {
                            error = error[0];
                        }

                        $.growl.error({message: error});
                    }
                });
        },

        __categoryApp__deleteCategory() {
            let self = this;

            swal({
                title: 'Are you sure?',
                text: 'Category and all subcategories will be deleted!',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#DD6B55',
                confirmButtonText: 'Delete!'
            }).then(function () {
                let route = window.categoryModule.routes.update.replace('--ID--', self.selectedNode.id);
                self.__categoryApp__blockPanel();

                $.post(route, {_method: 'DELETE'}).done(() => {
                    self.__categoryApp__loadCategories();

                    // Reset form
                    self.categoryForm = __mockForm();
                    self.categoryFormAction = 'create';
                    self.selectedNode = null;

                    setTimeout(() => {
                        swal('Success', 'Category successfully deleted!', 'success');
                    }, 300);
                }).fail(() => {
                    setTimeout(() => {
                        swal('Whoops..', 'Unable to delete category - server error!', 'error');
                    }, 300);
                });
            });
        },

        __categoryApp__blockPanel() {
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

        __categoryApp__unblockPanel() {
            $(this.$el).unblock();
        }
    },

    components: {
        'categories-tree': require('./components/CategoriesTree.vue'),
        'icon-select': require('./components/IconSelect.vue')
    }
});