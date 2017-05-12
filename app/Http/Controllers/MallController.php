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
use App\Model\WxTrade;
use App\Service\ApiService;
class MallController extends Controller
{
    // 售货机列表
    public function vmList(Request $request){
        phpinfo();
        exit;
        $wxId           = $request->session()->get('wxId');
        $mallService    = new MallService();
        $vmlist         = $mallService->getNodeList();
        return view('wx.vmList', array(
                'vms'           => $vmlist,
                'css_version'   => config::get('mg.css_version'),
                'js_version'    => config::get('mg.js_version'),
                'cdn_url'       => config::get('mg.cdn_url'),
                'host'          => Config::get('mg.host')
            ));
    }

    // 商品列表
    public function productsList(Request $request, $vmid){
        $wxId           = $request->session()->get('wxId');
        $mallService    = new MallService();
        $productsList   = $mallService->showPros($vmid, 'book');
        $vmInfor        = $mallService->getVmInfo($vmid);
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
                'cdn_url'       => config::get('mg.cdn_url'),
                'host'          => Config::get('mg.host')
            ));
    }

    // 商品详情
    public function productDetail($vmid, $pid){
        $mallService = new MallService();
        $detail = $mallService->getProDetail($pid, $vmid, 'book');

        if(empty($detail)){
            return view('wx.error', array(
                    'message'   => $detail['msg']
                ));
        }

        // 测试图片加载 Start
        if(!empty($detail)){
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
                'cdn_url'       => config::get('mg.cdn_url'),
                'host'          => Config::get('mg.host')
            ));
    }

    // 结算
    public function wxAccount(Request $request){
        $wxId    = $request->session()->get('wxId');
        $vmInfor = $request->session()->get('currentVM');

        // 获取用户上次订单的手机号
        $phone = User::getPhone($wxId);
        // echo '<pre>';
        // echo $wxId;
        // print_r($phone);
        // echo '</pre>';
        // exit;

        return view('wx.account', array(
                'vminfor'       => $vmInfor,
                'phone'         => $phone,
                'css_version'   => config::get('mg.css_version'),
                'js_version'    => config::get('mg.js_version'),
                'cdn_url'       => config::get('mg.cdn_url'),
                'host'          => Config::get('mg.host')
            ));
    }

    // 支付
    public function ajaxWxPay(Request $request){
        $data = $request->all();
        $mallService = new MallService();
        $total_price = 0;
        $retail_price = 0;
        $detail = null;
        $vmid = $data['vmid'];
        foreach ($data['products'] as $key => $value) {
            // 检查商品售价是否与服务器价格一致
            $detail = $mallService->getProDetail($value['pid'], $vmid, 'book');
            if($detail->retail_price != $value['rprice'] ){
                return json_encode(array(
                        'code'  => 400,
                        'pid'   => $detail->product_id,
                        'pname' => $detail->product_name,
                        'msg'   => '售价与服务器不符'
                    ));
            }
            $total_price = $total_price + $value['rprice'] * $value['count'];
        }

        // 查询卡券优惠信息
        if(!$data['card_id']){
            $retail_price = $total_price;
        }else{
            // 查看卡券信息
            // 计算实际售价
            // $retail_price = '';
            // 卡券核销
        }

        // array('product_id'=>1,product_name='鲜奶',original_price=>100,retail_price=>100,'num'=>2),
        $products = null;
        foreach ($data['products'] as $k => $v) {
            $products[] = array(
                    'product_id'    => $v['pid'],
                    'product_name'  => $v['pname'],
                    'original_price'=> $v['oprice'],
                    'retail_price'  => $v['rprice'],
                    'num'           => $v['count']
                );
        }

        //渠道：1扫码预定，2微信商城预定，3扫码购买，4微信商城购买
        $intention = array(
                'channel'       => 2,
                'vmid'          => $data['vmid'],
                'products'      => $products,
                'total_price'   => $total_price,
                'retail_price'  => $retail_price,
                'card_id'       => $data['card_id'],
                'card_name'     => $data['card_name'],
                'type'          => $data['type'],
                'rate'          => $data['rate']
            );

        $request->session()->put('intention', $intention);
        Log::debug('wxPay[intention:'.json_encode($request->session()->get('intention')).']');

        return json_encode(array(
                    'code'  => 200,
                    'msg'   => 'success'
                ));
        exit;
    }

    // 预定结果
    public function result(Request $request, $wxtId){
        $wxId           = $request->session()->get('wxId');
        $mallService    = new MallService();
        $apiService     = new ApiService(); 
        $wxTrade        = WxTrade::find($wxtId);

        if($wxTrade->openid != $wxId){
            return view('wx.error', array(
                    'message'   => '订单与本人不符'
                ));
        }

        $vmid           = $wxTrade->vmid;
        $vmInfor        = $mallService->getVmInfo($vmid);
        $orderId        = $wxTrade->order_id;
        $orderDetail    = $apiService->getOrderById($orderId);
        $order = array(
                'vmid'          => $orderDetail->vmid,
                'type'          => $orderDetail->type,
                'start_date'    => $orderDetail->start_date,
                'retail_price'  => $orderDetail->retail_price,
                'pay_status'    => $orderDetail->pay_status,
                'rate'          => $orderDetail->rate,
                'wx_id'         => $orderDetail->wx_id
            );

        // 联系电话
        $count = 0;
        $products = array();
        foreach ($orderDetail->products as $key => $value) {
            if(isset($products[$value->product_id])){
                $products[$value->product_id]['num'] ++;
            }else{
                $products[$value->product_id] = array(
                    'id'    => $value->product_id,
                    'pname' => $value->product_name,
                    'num'   => 1,
                    'volume' => $value->volume.($value->unit == 1 ? 'ml' : 'g')
                );
            }
            $count ++;
        }

        $order['products'] = $products;
        $order['count'] = $count;

        // 根据微信交易单号查看订单
        return view('wx.result', array(
                'vmInfor'   => $vmInfor,
                'order'     => $order
            ));
    }

    // 我的订单
    public function myorders(Request $request){
        // 获取用户信息
        $mallService    = new MallService();
        $wxId           = $request->session()->get('wxId');

        // 用户的配送中的订单列表


        return view('wx.myorders', array(
                
            ));
    }

    // 历史订单
    public function historyOrders(Request $request){
        $wxId           = $request->session()->get('wxId');

        return view('wx.history', array(

            ));
    }

}

