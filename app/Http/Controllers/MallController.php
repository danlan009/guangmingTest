<?php 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;

use DB; 
use Cache; 
use Log;
use App\Service\MallService;
class MallController extends Controller
{
  

    // 售货机列表
    public function vmList(){

        return view('wx.vmList', array(
                'vms' => 'test: vm list'
            ));
    }

    // 商品列表
    public function productsList($vmid){

        return view('wx.proList', array(
                'vmid' => $vmid
            ));
    }

    // 商品详情
    public function productDetail($pid){

        return view('wx.details', array(
                
            ));
    }

    // 预定结果
    public function result(){

        return view('wx.result', array(
                
            ));
    }

    // 我的订单
    public function myorders(){
        // 获取用户信息

        return view('wx.myOrders', array(
                
            ));
    }

    // 我的微信卡券列表
    public function wxCards(){
        $wxId = '';

        return view('wx.wxCards', array(
                
            ));
        
    }

    public function test(Request $request){
        $mallService = new MallService();
        $proList = $mallService->getVmList();
        dd($proList);
    } 

}

