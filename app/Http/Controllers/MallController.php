<?php 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;

use DB; 
use Cache; 
use Log;
use App\Service\MallService;
use App\Service\SupplyService;
use App\Service\StatService;
use App\Model\User;
class MallController extends Controller
{
    // 售货机列表
    public function vmList(){
        $mallService = new MallService();
        $vmlist = $mallService->getNodeList();
        return view('wx.vmList', array(
                'vms' => $vmlist
            ));
    }

    // 商品列表
    public function productsList($vmid){
        $mallService = new MallService();
        $productsList = $mallService->showPros($vmid, 'book');
        $vmInfor    = $mallService->getVmInfo($vmid);

        // echo '<pre>';
        // print_r($vmInfor);
        // print_r($productsList);
        // echo '</pre>';
        // exit;
        return view('wx.proList', array(
                'products'  => $productsList,
                'vmInfor'   => $vmInfor
            ));
    }

    // 商品详情
    public function productDetail($vmid, $pid){
        $mallService = new MallService();
        $detail = $mallService->getProDetail($pid, $vmid, 'book');

        // echo '<pre>';
        // print_r($detail);
        // echo '</pre>';

        return view('wx.details', array(
                'detail'   => $detail
            ));
    }

    // 预定结果
    public function result(){
        $mallService = new MallService();

        return view('wx.result', array(
                
            ));
    }

    // 我的订单
    public function myorders(){
        // 获取用户信息
        $mallService = new MallService();

        return view('wx.myOrders', array(
                
            ));
    }

    // 我的微信卡券列表 
    public function wxCards(){
        $wxId = '';

        return view('wx.wxCards', array(
                
            ));
        
    }

    // 结算
    public function wxAccount(){

    }

    public function test(){
        
    } 

}

