<template>
    <div class="categories-tree"></div>
</template>

<script>
    export default {
        props: ['route'],

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
                ref.jstree(true).settings.core.data = categories;
                ref.jstree(true).refresh();
            });
        }
    }
</script>