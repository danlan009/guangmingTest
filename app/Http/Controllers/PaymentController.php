<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use EasyWeChat\Foundation\Application;
use EasyWeChat\Payment\Order as wechatOrder;
class PaymentController extends Controller
{
    public function ajaxPrepay(Request $request){
    	$shopCar = $request->session()->get('shopCar'); // 预定通过购物车进入订单详情页存储session,再进入支付
    	if(empty($shopCar)){
    		return 'error';
    	}else{
    		$userId = $request->session()->get('userId');

    	}
    	$options = [
            'debug'  => true,
            'app_id' => env('WECHAT_APP_ID'),
            'secret' => env('WECHAT_SECRET'),
            'token'  => env('WECHAT_TOKEN'),
            // 'log' => [
            //     'level' => 'debug',
            //     'file'  => '/storage/logs/easywechat.log', // XXX: 绝对路径！！！！
            // ]
        ];
    	$wechat = new Application($options);
    	$paymentApi = $wechat->payment;
    	$attrs = [
    		'trade_type' => 'JSAPI',
    		'body' => $product['id'].'',
    	];
    }
}
