<template>
    <div id="categories-tree"></div>
</template>

<script>
    export default {
        props: ['route', 'editable'],

        data() {
            return {};
        },

        methods: {
            log(data) {
                console.log('[CategoriesTree]', data);
            }
        },

        mounted() {
            this.log('Mounted.');
        }
    }
</script>