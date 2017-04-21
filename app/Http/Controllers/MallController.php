<?php 
namespace App\Http\Controllers;

class MallController extends Controller{

	// 售货机列表
	public function vmList(){

		
		return view('wx.vmlist', array(
				'vms' => 'test: vm list'
			));
	}

	// 商品列表
	public function productsList($vmid){
		
	}

	// 商品详情
	public function productDetail($pid){

	}

	// 预定结果
	public function result(){

	}

	// 我的订单
	public function myorders(){
		// 获取用户信息
	}

	// 我的微信卡券列表
	public function wxCards(){
		$wxId = '';
		
	}

}