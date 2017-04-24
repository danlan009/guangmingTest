<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use EasyWeChat\Foundation\Application;
class CouponController extends Controller
{
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
    $app = new Application($options);
    $card = $app->card;
    $openid;
    $cardList = $card->getUserCards($openid);
    return json_encode($cardList);

}
