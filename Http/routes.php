<?php

Route::group([
    'prefix'     => 'admin',
    'as'         => 'category::',
    'middleware' => ['web', 'auth.admin'],
    'namespace'  => 'Modules\Category\Http\Controllers\Admin',
], function () {

    Route::post('categories/order', [
        'as'   => 'categories.order',
        'uses' => 'CategoryController@updateOrder',
    ]);

    Route::resource('categories', 'CategoryController', [
        'except' => ['create', 'edit', 'show'],
    ]);

});
