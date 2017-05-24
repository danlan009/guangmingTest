<?php
namespace App\Service;

use Illuminate\Http\Request;  

// use EasyWeChat\Foundation\Application;
use Log;
use Cache; 
use App\Lib\Http;
class CouponService{
    public static function getCardList($access_token){ //$app 为 application对象
        // 不使用esaywechat 
        $openid = session('wxId');
        $url  = 'https://api.weixin.qq.com/card/user/getcardlist?access_token='.$access_token;
        $d = array(
                'openid'    => $openid,
                'card_id'   => ''
                );
        // $openid = 'SIrewrv8f8UgNWp8u_qYwhwCM6s'; //测试
        $res = json_decode(Http::httpsPost($url, json_encode($d)),true);
        $cardList = [];
        if($res['errcode'] == 0){
            $cardList = $res['card_list'];
        }

        $newCardList = [];
        foreach ($cardList as $card) {
            $oneCard = [];
            $detail = self::getCardDetail($card['card_id'],$access_token);
            Log::debug('CouponService --- card_detail returns---'.json_encode($detail));
            $code = self::getCardCodeDetail($card['card_id'],$card['code'],$access_token);
            Log::debug('CouponService --- code_detail returns---'.json_encode($code));

            if($detail['errcode'] === 0){
                $oneCard['detail'] = $detail['card'];
                $oneCard['code'] = $card['code'];
            }

            if($code['errcode'] == 0 && $code['can_consume']){
                $oneCard['exp'] = $code['card'];
            }else{
                $oneCard['exp'] = '';
            }
            $newCardList[] = $oneCard;
        }
        Log::debug('CouponService---get user_card_list returns:'.json_encode($newCardList));
        return $newCardList;
    }
 
    public static function getCardDetail($card_id,$access_token){
        $key = 'WXCARD_'.$card_id.'_DETAIL_'.'gm';
        $data = Cache::get($key);
        if(empty($data)){
            $url = 'https://api.weixin.qq.com/card/get?access_token='.$access_token;
            $d  = array(
                    'card_id' => $card_id
                );
            $r = Http::httpsPost($url, json_encode($d));
            Log::debug('user_card_list:detail:'.$r);

            $data = json_decode($r, true);  
            Cache::put($key, $data, 1440);
        }
        return $data;
    }

    public static function getCardCodeDetail($card_id, $code, $access_token){
        $url = 'https://api.weixin.qq.com/card/code/get?access_token='.$access_token;
        $d          = array(
                'card_id'       => $card_id,
                'code'          => $code,
                'check_consume' => true
            );
        $r = Http::httpsPost($url, json_encode($d));
        Log::debug('user_card_list:code:'.$r);
        $data = json_decode($r, true);
        // Cache::put($key, $data, 1440);
        
        return $data;
    }


 
   
}
?>