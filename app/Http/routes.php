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
 
// 微信入口
Route::post('server',                            'ServerController@index');
Route::get('orders/daily_buy_codes/{vmId}',		'MallController@dailyBuyCodes');
Route::get('orders/daily_check_orders',			'MallController@dailyCheckOrders');

Route::get('supply/register',                   'SupplyController@register');
Route::post('supply/register_resolve',           'SupplyController@registerResolve');
Route::get('supply/get_supply_data',			'SupplyController@getSupplyData');
Route::get('supply/add',						'SupplyController@add');
Route::get('supply/get_daily_orders_to_send',	'SupplyController@getDailyOrdersToSend');
Route::get('isAbleToBook',						'OrderController@isAbleToBook');
Route::get('supply/start_supplyment',			'SupplyController@startSupplyment');
Route::get('supply/list_skus',					'SupplyController@listSkus');

Route::post('supply/ajax_receive_data',			'SupplyController@ajaxReceiveData');
Route::get('supply/ajax_clear',					'SupplyController@ajaxClear'); // 补货完成入口

Route::get('qr/create',							'QrCodeController@create');
Route::get('test',								'SupplyController@test'); 

Route::get('task/flush_cache',					'TaskController@flushCache');
Route::get('task/session_unset',				'TaskController@sessionUnset');
Route::get('task/updateImg',					'TaskController@updateImg'); // 检测图片是否修改

Route::get('task/check_orders',					'TaskController@dailyCheckOrders');
//测试通知 程洋
Route::get('myTest','SupplyController@myTest');

Route::group(['middleware' =>['web','wechat.oauth', 'wxAuth'] ], function(){

	Route::get('wx/vmlist', 					'MallController@vmList');
	Route::get('wx/list/{vmid}', 				'MallController@productsList');
	Route::get('wx/detail/{vmid}/{pid}', 		'MallController@productDetail');
	Route::get('wx/result/{wxid}', 				'MallController@result');
	Route::get('wx/orders', 					'MallController@myorders');
	Route::get('wx/history', 					'MallController@historyOrders');
	Route::get('wx/cards', 						'MallController@wxCards');
	Route::get('wx/account/{vmid}', 			'MallController@wxAccount');
	Route::get('wx/ajax_check_wxpay', 			'MallController@ajaxWxPay');
	Route::get('wx/order_details/{orderId}', 	'MallController@orderDetails');
	Route::get('wx/ajax_pause_delivery', 		'MallController@ajaxStopDate');
	Route::get('wx/ajax_continue_delivery', 	'MallController@ajaxUpdateDate');
	Route::get('wx/ajax_get_dates', 			'MallController@ajaxGetDates');

    Route::get('wx/ajax_prepay',                'PaymentController@ajaxPrepay');
    Route::get('wx/test',                       'PaymentController@test');

    Route::get('wx/ajax_consume_card' ,			'CouponController@ajaxConsumeCard');

});

Route::post('wx/notify_payment',                'PaymentController@notifyPayment');
Route::post('link/notify_vm_msg',               'GmLinkController@notifyVmMsg');

Route::get('phpinfo',							'SupplyController@phpinfo');
