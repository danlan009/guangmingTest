<?php
namespace App\Service;

use Illuminate\Http\Request;  
use App\Http\Requests;
use Log;
use EasyWeChat\Foundation\Application;
class CouponService{
    public function getCardList($app){
        
        $card = $app->card;
        $openid = session('wxId');
        dd($openid);
        if(!$openid){
            return '403未授权!';
        }
        $cardList = $card->getUserCards($openid);
        return $cardList;
    }
   
}
?>