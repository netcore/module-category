<template>
    <div class="categories-tree"></div>
</template>

<script>
    import _ from 'lodash';
    import EventBus from '../event-bus';

    export default {
        data() {
            return {};
        },

        mounted() {
            let self = this;
            let maxLevel = this.$parent.group.levels || -1;

            const jsTree = $(this.$el).jstree({
                opened: true,
                core: {
                    check_callback: (operation, node, parent, position, more) => {
                        if (operation === 'move_node' && maxLevel) {
                            return parent.parents.length < maxLevel;
                        }

                        return true;
                    },
                    data: []
                },
                plugins: ['dnd']
            });

            // ---------------------------- JS Tree events --------------------------//
            jsTree.on('select_node.jstree', (event, data) => {
                EventBus.$emit('jstree::node-selected', data.node);
            });

            jsTree.on('move_node.jstree', (event, data) => {
                let treeJson = jsTree.jstree(true).get_json('#', {
                    no_state: true,
                    no_data: true,
                    no_li_attr: true,
                    no_a_attr: true
                });

                EventBus.$emit('jstree::node-moved', {
                    tree: self.buildTreeDataForNestedSet(treeJson),
                    moved: data.node.id
                });
            });

            // ---------------------------- Catch outside events --------------------------//
            EventBus.$on('jstree::deselect-nodes', () => {
                jsTree.jstree('deselect_all', true);
            });

            EventBus.$on('jstree::reload-tree', () => {
                jsTree.jstree('refresh');
            });

            EventBus.$on('jstree::set-categories', categories => {
                let data = JSON.parse(
                    JSON.stringify(categories)
                );

                data = _.map(data, category => {
                    category.icon = 'fa fa-folder';
                    return category;
                });

                jsTree.jstree(true).settings.core.data = data;
                jsTree.jstree(true).refresh();
            });
        },

        methods: {
            /**
             * Build nested structure of jsTree.
             *
             * @param treeJson
             * @return {Array}
             */
            buildTreeDataForNestedSet(treeJson) {
                let self = this;
                let data = [];

                _.each(treeJson, category => {
                    let cat = {id: category.id};

                    if (category.children && category.children.length) {
                        cat.children = self.buildTreeDataForNestedSet(category.children);
                    }

                    data.push(cat);
                });

                return data;
            }
        }
    };
</script>