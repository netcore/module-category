<?php

Route::group([
    'prefix'     => 'admin',
    'as'         => 'category::',
    'middleware' => ['web', 'auth.admin'],
    'namespace'  => 'Modules\Category\Http\Controllers\Admin'
], function() {

    Route::resource('categories', 'CategoryController');

});
