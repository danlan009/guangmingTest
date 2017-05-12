<?php
namespace App\Http\Middleware;
use Illuminate\Http\Request;
use Closure;

use App\Model\User;
class wxAuth{

	public function handle($request, Closure $next){
		// 微信鉴权
		$oauthUser = session('wechat.oauth_user'); // esay-wechat自动配置session
		if(!empty($oauthUser)){
			$openid = $oauthUser->getId(); // 获取openid
			$nick_name = $oauthUser->getNickname();
			$password = User::createPassword($openid);
			$model = User::firstOrCreate([
											['wx_id' => $openid],
											[
												'wx_id' => $openid,
												'wx_name' => $nick_name,
												'password' => $password
											]
										]);
			\Log::debug('new user created ---'.json_encode($model));
			session(['openid',$openid]);
		}
		return $next($request);
	}

}