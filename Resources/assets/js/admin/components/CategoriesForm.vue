<template>
    <form class="panel panel-default m-b-0" @submit.prevent="submitHandler()">
        <input type="hidden" name="parent_id" :value="selectedNode.id">

        <div class="panel-heading">
            <span class="panel-title">
                {{ action === 'edit' ? 'Edit' : 'Create' }} category
            </span>

            <div class="panel-heading-controls" v-if="action === 'edit'">
                <button type="button" class="btn btn-xs btn-success" @click="addChild" :disabled="!isAbleToCreateChild">
                    <i class="fa fa-plus-circle"></i> Add child
                </button>

                <button type="button" class="btn btn-xs btn-warning" @click="cancelEditing">
                    <i class="fa fa-times-circle"></i> Cancel editing
                </button>

                <button type="button" class="btn btn-xs btn-danger" @click="deleteCategory">
                    <i class="fa fa-trash"></i> Delete category
                </button>
            </div>
        </div>

        <div class="panel-body">
            <div class="form-group" v-if="showSelectedParentCategory">
                <label for="selectedNodeName">Parent category:</label>
                <div class="input-group">
                    <input type="text" class="form-control" v-model="selectedNode.text" disabled id="selectedNodeName">

                    <div class="input-group-btn">
                        <button type="button" class="btn btn-danger" @click="deselectCurrentNode()">
                            <i class="fa fa-times"></i> Create as root
                        </button>
                    </div>
                </div>
            </div>

            <template v-if="hasMoreLanguages">
                <ul class="nav nav-tabs">
                    <li v-for="({ title, iso_code: iso }) in $parent.languages"
                        :class="{ 'active' : !Object.keys($parent.languages).indexOf(iso) }">
                        <a data-toggle="tab" :href="'#translations-' + iso">{{ title }}</a>
                    </li>
                </ul>
            </template>

            <div class="tab-content">
                <div v-for="({ iso_code: iso }) in $parent.languages"
                     class="tab-pane fade"
                     :id="'translations-' + iso"
                     :class="{'in active' : !Object.keys($parent.languages).indexOf(iso)}"
                >
                    <div class="form-group">
                        <label class="control-label">Category name: <span class="color-red">*</span></label>
                        <input type="text" class="form-control"
                               :name="'translations[' + iso + '][name]'"
                               v-model="categoryForm.translations[iso].name">
                    </div>

                    <div class="form-group">
                        <label class="control-label">Category slug:</label>
                        <input type="text" class="form-control"
                               :name="'translations[' + iso + '][slug]'"
                               v-model="categoryForm.translations[iso].slug">
                    </div>
                </div>
            </div>

            <div class="form-group" v-if="false">
                <label for="icon">Icon:</label>
                <icon-select id="icon" :icon="categoryForm.icon" v-if="group.icons_type === 'select2'"></icon-select>
                <input type="file" name="file_icon" class="form-control" id="icon" v-else>
            </div>
        </div>

        <div class="panel-footer text-right">
            <button class="btn btn-success">
                <template v-if="action === 'edit'"><i class="fa fa-save"></i> Save</template>
                <template v-else><i class="fa fa-plus-circle"></i> Create</template>
            </button>
        </div>
    </form>
</template>

<script>
    import _ from 'lodash';
    import axios from 'axios';
    import EventBus from '../event-bus';
    import FormMock from '../form-mock';

    export default {
        props: [
            'category'
        ],

        data() {
            return {
                action: 'create',
                selectedNode: {},
                selectedNodeDepth: 1,
                categoryForm: FormMock()
            };
        },

        /**
         * Created event.
         */
        created() {
            this.setupEventListeners();
        },

        /**
         * Component methods.
         */
        methods: {
            /**
             * Setup events.
             */
            setupEventListeners() {
                EventBus.$on('jstree::node-selected', this.setCurrentNode);
            },

            /**
             * Create category as child.
             */
            addChild() {
                this.action = 'create';
                this.categoryForm = FormMock();
            },

            /**
             * Cancel category editing.
             */
            cancelEditing() {
                this.action = 'create';
                this.categoryForm = FormMock();
                this.selectedNode = {};

                EventBus.$emit('jstree::deselect-nodes');
            },

            /**
             * Cancel adding as child, set as root.
             */
            deselectCurrentNode() {
                this.selectedNode = {};
                EventBus.$emit('jstree::deselect-nodes');
            },

            deleteCategory() {
                this.showDeleteConfirmationPopup().then(() => {
                    EventBus.$emit('categories::block-panel');

                    let route = this.$parent.routes.destroy.replace('-ID-', this.selectedNode.id);

                    axios.post(route, { _method: 'DELETE' }).then(() => {
                        EventBus.$emit('categories::reload-and-unblock');
                        this.cancelEditing();
                    }).catch(() => {
                        $.growl.error({ message: 'Whoops.. Something went wrong!' });
                        EventBus.$emit('categories::unblock-panel');
                    });

                }).catch(_.noop);
            },

            /**
             * Show delete confirmation popup.
             *
             * @return {Promise}
             */
            showDeleteConfirmationPopup() {
                return swal({
                    title: 'Are you sure?',
                    text: 'Category and all subcategories will be deleted!',
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#DD6B55',
                    confirmButtonText: 'Delete!'
                });
            },

            /**
             * Set active category and node.
             */
            setCurrentNode(node) {
                let nodeData = _.find(this.$parent.categories, {id: parseInt(node.id)});

                this.selectedNode = nodeData;
                this.categoryForm = FormMock(nodeData);
                this.action = 'edit';
                this.selectedNodeDepth = node.parents.length;
            },

            /**
             * Form submit handler.
             */
            submitHandler() {
                EventBus.$emit('categories::block-panel');

                let route = this.$parent.routes[this.action === 'create' ? 'store' : 'update'].replace('-ID-', this.categoryForm.id);
                let method = this.action === 'create' ? 'POST' : 'PUT';

                let formData = new FormData(
                    this.$el
                );

                formData.append('_method', method);

                axios.post(route, formData).then(res => {
                    console.log(res);
                }).catch(err => {
                    if (err.response && err.response.status === 422) {
                        this.showValidationError(err.response.data);
                    } else {
                        alert('Whoops.. Something went wrong!');
                    }
                }).then(() => {
                    EventBus.$emit('categories::reload-and-unblock');
                });
            },

            /**
             * Display validation error.
             */
            showValidationError(data) {
                $.growl.error({
                    title: 'Validation error!',
                    message: data.errors[Object.keys(data.errors)[0]][0]
                });
            }
        },

        /**
         * Computed properties.
         */
        computed: {
            /**
             * Check if parent should be shown.
             *
             * @return {boolean}
             */
            showSelectedParentCategory() {
                return this.action === 'create' && this.selectedNode.id;
            },

            /**
             * Check if application has more than one language.
             *
             * @return {boolean}
             */
            hasMoreLanguages() {
                return _.size(this.$parent.languages) > 1;
            },

            /**
             * Check if user is able to create child category within current category.
             *
             * @return {boolean}
             */
            isAbleToCreateChild() {
                let maxLevel = this.$parent.group.levels;

                if (!maxLevel) {
                    return true;
                }

                return this.selectedNodeDepth < maxLevel;
            }
        }
    };
</script>
