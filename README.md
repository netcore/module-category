## Module for managing site categories
This module was made for easy management of categories.

## Features

- Everything is translatable
- Each category has slug, manual slugs are allowed
- Drag'n'drop support for reordering

## Pre-installation

This module is part of Netcore CMS ecosystem and is only functional in a project that has following packages installed:

1. https://github.com/netcore/netcore
2. https://github.com/netcore/module-admin
3. https://github.com/netcore/module-translate

### Installation

 - Require this package using composer
```
    composer require netcore/module-category
```

 - Publish assets/configuration/migrations
```
    php artisan module:publish Category
    php artisan module:publish-config Category
    php artisan module:publish-migration Category
    php artisan migrate
```

### Configuration

 - Configuration file is available at config/netcore/module-category.php

#### Icon set

- To use "FontAwesome" icon set, set config to
```php
    return [
        'icons' => [
            ...
            'presenter' => \Modules\Category\Icons\FontAwesomeIconSet::class,
            ...
        ]
        ...
    ];
```

- To use "Ionicons" icon set, set config to
```php
    return [
        'icons' => [
            ...
            'presenter' => \Modules\Category\Icons\IonIconSet::class,
            ...
        ]
        ...
    ];
```

- Also you can create your own icon presenter class. It should implement \Modules\Category\Icons\IconSetInterface
```php
    use Modules\Category\Icons\IconSetInterface;

    class CustomIconSet implements IconSetInterface
    {
        /**
         * Get array of available icons
         *
         * @return array
         */
        public function getIcons(): array {
            return [
                // Class    => Text
                'my-icon-1' => 'My Icon 1',
                'my-icon-2' => 'My Icon 2'
            ];
        }

        /**
         * Get template for select2 render
         *
         * @return string
         */
        public function getSelect2Template(): string {
            return '<i class="::class::"></i><span>::text::</span>';
        }

        /**
         * Get styles to inject
         *
         * @return array
         */
        public function getInjectableStyles(): array {
            return [
                '/link/to/your/css/style.css'
            ];
        }

        /**
         * Get sprite to inject before container
         *
         * @return string
         */
        public function getInjectableSprite(): string {
            return ''; // this is needed when using SVG icons. ex.: return view('svg/sprite')->render();
        }
    }
```
