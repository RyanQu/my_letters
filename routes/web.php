<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Auth::routes();

Route::get('/', 'HomeController@index');

Route::get('/home', 'HomeController@index');

Route::post('/home/order', 'HomeController@order');

Route::get('now', function () {
    return date("Y-m-d H:i:s");
});
Auth::routes();

Route::get('/home', 'HomeController@index');
