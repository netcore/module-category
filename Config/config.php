<?php

return [
    'icons' => [
        /**
         * Enable/disable category icons
         */
        'enabled'   => true,

        /**
         * Only root categories have icons?
         */
        'rootOnly' => true,

        /**
         * Select2 icon presenter/formatter
         */
        'presenter' => \Modules\Category\Icons\FontAwesomeIconSet::class,
    ],
];
