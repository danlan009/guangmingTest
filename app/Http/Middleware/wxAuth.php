<?php
namespace App\Http\Middleware;
use Illuminate\Http\Request;
use Closure;
use App\Service\MallService;
use Log;

class wxAuth{

	public function handle(Request $request, Closure $next){

		$oauthUser = session('wechat.oauth_user');
        if($oauthUser){
            $wxUser = WxUser::getUserByWxId($oauthUser->getId());
            Log::debug('wxauth:'.json_encode($wxUser));
            
            $password = (new MallService())->createBlno();
            if(empty($wxUser)){//新的微信用户
                $user = User::create();
                $wxUser = User::create([
                    'user_id'   => $user->id,
                    'wx_id'    	=> $oauthUser->getId(),
                    'wx_name'  	=> $oauthUser->getNickname(),
                    'password'  => $password
                ]);
            }
            $request->session()->set('userId', $wxUser->user_id);
            $request->session()->set('wxId', $oauthUser->getId());
        }else if(env('APP_ENV')=='local'){
            $request->session()->put('wxId', 'test');
        }

		return $next($request);
	}

}