<template>
    <select :id="id" title="Icon">
        <option value="">No icon</option>
        <option :value="id" v-for="(icon, id) in icons">{{ icon }}</option>
    </select>
</template>

<script>
    export default {
        props: ['id', 'icon'],

        data() {
            return {
                options: {},
                icons: window.categoryModule.icons.set
            };
        },

        mounted() {
            const iconTemplate = window.categoryModule.icons.template;

            const formatOutput = (icon) => {
                if (!icon.id || !icon.id.length) {
                    return 'No icon';
                }

                return $(iconTemplate.replace(/::text::/g, icon.text).replace(/::id::/g, icon.id));
            };

            this.options.templateResult = formatOutput;
            this.options.templateSelection = formatOutput;

            this.options.escapeMarkup = (m) => {
                return m;
            };

            let parent = this.$parent;

            $(this.$el)
                .val(this.icon)
                .select2(this.options)
                .on('select2:select', (data) => {
                    parent.categoryForm.icon = data.params.data.id;
                });
        },
        
        beforeDestroy() {
            $(this.$el).select2('destroy');
        },

        watch: {
            icon: function (value) {
                $(this.$el).val(value).trigger('change');
            }
        }
    }
</script>