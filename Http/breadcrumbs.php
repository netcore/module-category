<?php

use DaveJamesMiller\Breadcrumbs\Facades\Breadcrumbs;

Breadcrumbs::register('admin.categories', function($breadcrumbs) {
    $breadcrumbs->parent('admin');
    $breadcrumbs->push('Categories', route('category::categories.index'));
});