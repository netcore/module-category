<?php

return [
    'icons'     => [
        /**
         * Enable/disable category icons
         */
        'enabled'   => true,

        /**
         * Only root categories have icons?
         */
        'root_only' => true,

        /**
         * Select2 icon presenter/formatter
         */
        'presenter' => \Modules\Category\Icons\FontAwesomeIconSet::class,
    ],

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
    ],
];
