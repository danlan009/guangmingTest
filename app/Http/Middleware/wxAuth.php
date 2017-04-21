<?php
namespace App\Http\Middleware;
use Illuminate\Http\Request;
use Closure;

class wxAuth{

	public function handle($request, Closure $next){
		// 微信鉴权
		return $next($request);
	}

}