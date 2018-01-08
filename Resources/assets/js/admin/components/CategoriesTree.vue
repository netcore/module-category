<template>
    <div class="categories-tree"></div>
</template>

<script>
    export default {
        data() {
            return {};
        },

        mounted() {
            let ref = $(this.$el).jstree({
                opened: true,
                plugins: ['dnd'],
                core: {
                    check_callback: true,
                    data: []
                }
            });

            let self = this;

            // On node selection, load data to form
            ref.on('select_node.jstree', (event, data) => {
                self.$parent.$emit('jsTree.nodeSelected', data.node);
            });

            // Category order changed
            ref.on('move_node.jstree', (event, data) => {
                let treeJson = ref.jstree(true).get_json('#', {
                    no_state: true,
                    no_data: true,
                    no_li_attr: true,
                    no_a_attr: true
                });

                self.$parent.$emit('jsTree.orderChanged', {
                    tree: self.buildTreeDataForNestedSet(treeJson),
                    moved: data.node.id
                });
            });

            // Deselect current selection
            self.$parent.$on('jsTree.deselectAllNodes', () => {
                ref.jstree('deselect_all', true);
            });

            // Reload category tree
            self.$parent.$on('jsTree.reloadTree', () => {
                ref.jstree('refresh');
            });

            // Reload tree
            self.$parent.$on('jsTree.categoriesLoaded', categories => {
                let data = JSON.parse(JSON.stringify(categories)); // To plain object

                data = _.map(data, category => {
                    category.icon = 'fa fa-folder';
                    return category;
                });

                ref.jstree(true).settings.core.data = data;
                ref.jstree(true).refresh();
            });
        },

        methods: {
            buildTreeDataForNestedSet(treeJson) {
                let self = this;
                let data = [];

                _.each(treeJson, category => {
                    let cat = { id: category.id };

                    if(category.children && category.children.length) {
                        cat.children = self.buildTreeDataForNestedSet(category.children);
                    }

                    data.push(cat);
                });

                return data;
            }
        }
    }
</script>