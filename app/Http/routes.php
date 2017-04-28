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
 
Route::get('orders/daily_buy_codes/{vmId}',		'MallController@dailyBuyCodes');
Route::get('orders/daily_check_orders',			'MallController@dailyCheckOrders');

Route::get('supply/get_supply_data',			'SupplyController@getSupplyData');
Route::get('supply/add',						'SupplyController@add');
Route::get('supply/get_daily_orders_to_send',	'SupplyController@getDailyOrdersToSend');
Route::get('isAbleToBook',						'OrderController@isAbleToBook');

Route::get('qr/create',							'qrCodeController@create');
Route::get('test',								'SupplyController@test');

Route::get('card/getCardList',					'CoupouController@getCardList');

Route::get('task/flush',						'TaskController@flushCache');
Route::get('task/updateImg',					'TaskController@updateImg'); // 检测图片是否修改

// Route::group(['middleware' => 'wxAuth'], function(){

	Route::get('wx/vmlist', 					'MallController@vmList');
	Route::get('wx/list/{vmid}', 				'MallController@productsList');
	Route::get('wx/detail/{vmid}/{pid}', 		'MallController@productDetail');
	Route::get('wx/result', 					'MallController@result');
	Route::get('wx/orders', 					'MallController@myorders');
	Route::get('wx/cards', 						'MallController@wxCards');
	Route::get('wx/account', 					'MallController@wxAccount');

// });

