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

    Route::get('categories', [
        'as'   => 'categories.index',
        'uses' => 'CategoryController@index',
    ]);

    Route::get('categories/{categoryGroup}', [
        'as'   => 'categories.fetch',
        'uses' => 'CategoryController@fetchCategories',
    ]);

    Route::post('categories/{categoryGroup}', [
        'as'   => 'categories.store',
        'uses' => 'CategoryController@store',
    ]);

    Route::put('categories/{categoryGroup}/{category}', [
        'as'   => 'categories.update',
        'uses' => 'CategoryController@update',
    ]);

    Route::delete('categories/{categoryGroup}/{category}', [
        'as'   => 'categories.destroy',
        'uses' => 'CategoryController@destroy',
    ]);
});
