'use strict';

import _ from 'lodash';
import axios from 'axios';

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
            const rootOnly = window.categoryModule.icons.rootOnly;
            const action = this.categoryFormAction;

            if (!isEnabled) {
                return false;
            }

            // Create as root form
            if(rootOnly && action === 'create' && this.selectedNode) {
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

        this.$on('jsTree.orderChanged', treeJson => {
            axios.post(window.categoryModule.routes.order, treeJson).catch(err => {
                $.growl.error({message: 'Unable to save order - server error!'});
                console.log(err.response);
            });
        });
    },

    methods: {
        __categoryApp__loadCategories() {
            let self = this;

            axios.get(window.categoryModule.routes.index).then(res => {
                self.categories = _.keyBy(res.data, 'id');
                self.$emit('jsTree.categoriesLoaded', res.data);
            }).catch(() => {
                alert('Unable to load categories - server error!');
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
        __categoryApp__saveCategory(event) {
            const button = $(event.target);
            button.data('loading-text', '<i class="fa fa-spin fa-spinner"></i>').button('loading');

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
                .then(res => {
                    $.growl.success({message: 'Category saved!'});
                    self.__categoryApp__loadCategories();
                    button.button('reset');
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

                    button.button('reset');
                });
        },

        __categoryApp__deleteCategory() {
            let self = this;

            swal({
                title: "Are you sure?",
                text: "Category and all subcategories will be deleted!",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Delete!"
            }).then(function () {
                let route = window.categoryModule.routes.update.replace('--ID--', self.selectedNode.id);

                $.post(route, {_method: 'DELETE'}).done(() => {
                    self.__categoryApp__loadCategories();

                    // Reset form
                    self.categoryForm = __mockForm();
                    self.categoryFormAction = 'create';
                    self.selectedNode = null;

                    setTimeout(() => {
                        swal("Success", "Category successfully deleted!", "success");
                    }, 300);
                }).fail(() => {
                    setTimeout(() => {
                        swal("Whoops..", "Unable to delete category - server error!", "error");
                    }, 300);
                });
            }).catch(() => {
            });
        },
    },

    components: {
        'categories-tree': require('./components/CategoriesTree.vue'),
        'icon-select': require('./components/IconSelect.vue')
    }
});