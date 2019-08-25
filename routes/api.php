<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('guest')->group(function () {
    Route::post('register', 'API\UserController@register');
    Route::post('login', 'API\UserController@login');
    Route::get('users/verify/{token}', 'API\UserController@verify')->name('verify');

});
Route::middleware('guest')->group(function () {
    Route::resource('books', 'API\BookController');

    Route::resource('users', 'API\UserController',
        [
            'except' => ['create', 'edit']
        ]
    );

    Route::delete('users/deactivated/{user}', 'API\UserController@deactivated');

    Route::delete('users/activated/{user}', 'API\UserController@activated');

    Route::get('deactivatedUsers', 'API\UserController@deactivatedUsers');

    Route::get('users/{user}/resend', 'API\UserController@resend')->name('resend');
});
