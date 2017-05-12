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
		// $request->session()->flush();
		$oauthUser = session('wechat.oauth_user'); // esay-wechat自动配置session
		// Log::debug('')
		Log::debug('wxAuth---oauthUser returns---'.json_encode($oauthUser));
		// if(!empty($oauthUser)){
		// 	$openid = $oauthUser->getId(); // 获取openid
		// 	$nick_name = $oauthUser->getNickname();
		// 	$password = User::createPassword($openid);
		// 	$model = User::firstOrCreate([
		// 									['wx_id' => $openid],
		// 									[
		// 										'wx_id' => $openid,
		// 										'wx_name' => $nick_name,
		// 										'password' => $password
		// 									]
		// 								]);
		// 	Log::debug('new user created ---'.json_encode($model));
		// 	session(['openid',$openid]);
		// }
		return $next($request);
	}

}