<?php

Route::group([
    'prefix'     => 'admin/category',
    'as'         => 'admin::category.',
    'middleware' => ['web', 'auth.admin'],
    'namespace'  => 'Modules\Category\Http\Controllers\Admin'
], function() {

    Route::resource('categories', 'CategoryController');

});
