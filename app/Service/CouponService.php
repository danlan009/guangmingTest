<?php
namespace App\Service;

use Illuminate\Http\Request;  
use App\Http\Requests;
use Log;
class CouponService{
    public getCardList(Request $request){
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
        $openid = $request->session->get('wxId');
        if(!$openid){
            return '403未授权!';
        }
        $cardList = $card->getUserCards($openid);
        return json_encode($cardList);
    }
   
}
?>