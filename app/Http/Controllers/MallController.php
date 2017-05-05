<?php 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;

use DB; 
use Cache; 
use Log;
use Config;
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
                'vms'           => $vmlist,
                'css_version'   => config::get('mg.css_version'),
                'js_version'    => config::get('mg.js_version'),
                'cdn_url'       => config::get('mg.cdn_url')
            ));
    }

    // 商品列表
    public function productsList(Request $request, $vmid){

        $mallService = new MallService();
        $productsList = $mallService->showPros($vmid, 'book');
        $vmInfor    = $mallService->getVmInfo($vmid);
        $request->session()->put('currentVM', $vmInfor);

        // 测试图片加载
        if(!empty($productsList)){
            $productsList[0]->pic_l = "/sources/images/products/100017_l.jpg";
            $productsList[1]->pic_l = "/sources/images/products/100018_l.jpg";
            $productsList[2]->pic_l = "/sources/images/products/100016_l.jpg";
        }

        return view('wx.proList', array(
                'products'      => $productsList,
                'vmInfor'       => $vmInfor,
                'css_version'   => config::get('mg.css_version'),
                'js_version'    => config::get('mg.js_version'),
                'cdn_url'       => config::get('mg.cdn_url')
            ));
    }

    // 商品详情
    public function productDetail($vmid, $pid){
        $mallService = new MallService();
        $detail = $mallService->getProDetail($pid, $vmid, 'book');
        
        // 测试图片加载 Start
        if($detail != 'error'){
            $detail->pic_t = "/sources/images/products/100016_d.jpg";
            $detail->detail_pics = array(
                    '/sources/images/details/img_1_1.jpg',
                    '/sources/images/details/img_1_2.jpg',
                    '/sources/images/details/img_1_1.jpg'
                );
        }
        // 测试图片加载 End

        return view('wx.details', array(
                'detail'        => $detail,
                'vmid'          => $vmid,
                'css_version'   => config::get('mg.css_version'),
                'js_version'    => config::get('mg.js_version'),
                'cdn_url'       => config::get('mg.cdn_url')
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
    public function wxAccount(Request $request){
        $vmInfor = $request->session()->get('currentVM');
        return view('wx.account', array(
                'vminfor'        => $vmInfor,
                'css_version'   => config::get('mg.css_version'),
                'js_version'    => config::get('mg.js_version'),
                'cdn_url'       => config::get('mg.cdn_url')
            ));
    }

    public function test(){
        
    } 

}

