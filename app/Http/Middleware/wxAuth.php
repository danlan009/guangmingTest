<?php
namespace App\Http\Middleware;
use Illuminate\Http\Request;
use Closure;
use App\Service\MallService;
use Log;

use App\Model\User;
class wxAuth{

	public function handle($request, Closure $next){
		// 微信鉴权
		$oauthUser = session('wechat.oauth_user'); // esay-wechat自动配置session
		Log::debug('wxAuth---oauthUser returns---'.json_encode($oauthUser));
		if(!empty($oauthUser)){
			$openid = $oauthUser->getId(); // 获取openid
			$nick_name = $oauthUser->getNickname();
			$user = User::where('wx_id',$openid)->get()->toArray();
			if(empty($user)){
				$newUser = User::create([
								'wx_id' => $openid,
								'wx_name' => $nick_name, 
							]);
				User::createPassword($openid);
				Log::debug('new user created ---'.json_encode($newUser));
			}

			$request->session()->put('wxId',$openid);

		}else if(env('APP_ENV')=='local'){
            $request->session()->set('wxId', 'local_test');
            return $next($request);
        }

		return $next($request);
	}

}