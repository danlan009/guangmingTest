<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::group(['middleware' => 'wxAuth'], function(){

	Route::get('wx/vmlist', 		'MallController@vmList');
	Route::get('wx/list/{vmid}', 	'MallController@productsList');
	Route::get('wx/detail/{pid}', 	'MallController@productDetail');
	Route::get('wx/result', 		'MallController@result');
	Route::get('wx/orders', 		'MallController@myorders');
	Route::get('wx/cards', 			'MallController@wxCards');

});