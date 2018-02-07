<?php

return [
    /**
     * Cache tag name.
     *
     * Set to false to disable cache clearing after creating/editing/reordering categories.
     */
    'cache_tag' => 'categories',

    /**
     * JsTree config
     */
    'tree'      => [
        /**
         * Are all nodes opened by default?
         */
        'opened_by_default' => false,

        /**
         * Append category name with some data using helper function.
         * Useful for related items count in category etc.
         * Set to null to disable.
         */
        'name_suffix_helper_function' => null,
    ],
];
