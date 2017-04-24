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


Route::get('user/create_password','UserController@createPassword');
Route::get('user/save_user','UserController@saveUser');
Route::get('deliver/get_sender_list','SupplyController@getSenderList');
Route::get('deliver/get_info_from_json','SupplyController@getInfoFromJson');

Route::get('mall/list_all_pro','MallController@listAllPros');
Route::get('mall/show_pros','MallController@showPros'); //售货机商品展示页(公众号选择售货机或者扫码进入)
Route::get('api/pro_detail','MallController@getProDetail');
Route::get('daily_orders/get_daily_orders','MallController@getDailyOrders');
Route::get('daily_orders/daily_buy_codes','MallController@dailyBuyCodes');
Route::get('daily_orders/daily_check_orders','MallController@dailyCheckOrders');

Route::get('supply/get_supply_data','SupplyController@getSupplyData');
Route::get('supply/add','SupplyController@add');
Route::get('supply/get_daily_orders_to_send','SupplyController@getDailyOrdersToSend');
Route::get('isAbleToBook','OrderController@isAbleToBook');

Route::get('qr/create','qrCodeController@create');
Route::get('test','MallController@test');

Route::group(['middleware' => 'wxAuth'], function(){

	Route::get('wx/vmlist', 		'MallController@vmList');
	Route::get('wx/list/{vmid}', 	'MallController@productsList');
	Route::get('wx/detail/{pid}', 	'MallController@productDetail');
	Route::get('wx/result', 		'MallController@result');
	Route::get('wx/orders', 		'MallController@myorders');
	Route::get('wx/cards', 			'MallController@wxCards');

});

