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
use App\Model\OrderStop;
use App\Service\ApiService;
use EasyWeChat\Foundation\Application;
use App\Service\CouponService;
class MallController extends Controller
{
    // 售货机列表
    public function vmList(Request $request){
        // $request->session()->put('wxId', 'oSIrewoj-pLOhd5ef29VLuM8sLAs');
        $wxId           = $request->session()->get('wxId');
        $mallService    = new MallService();
        $vmlist         = $mallService->getNodeList();
        Log::debug('MallController [vmlist: '.json_encode($vmlist).'][wxid:'.$wxId.']');
        return view('wx.vmList', array(
                'vms'           => $vmlist,
                'channel'       => $request->session()->get('accessChannel'),
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

        //渠道：1扫码预定，2微信商城预定，3扫码购买，4微信商城购买
        $channel        = $request->input('c') ? $request->input('c') : '1';
        $request->session()->put('accessChannel', $channel);
        // echo $request->session()->get('accessChannel');
        // exit;

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
    public function wxAccount(Request $request, Application $app, $vmid){
        $wxId    = $request->session()->get('wxId');
        $vmInfor = $request->session()->get('currentVM');
        $access_token = $app->access_token;
        $access_token = $access_token->getToken();
        $cardList = CouponService::getCardList($access_token);
        Log::debug('wxAccount:[cardList:'.json_encode($cardList).']');
        $phone = User::getPhone($wxId);
        // var_dump($cardList);
        // echo 'dev';
        // exit;
        return view('wx.account', array(
                'vminfor'       => $vmInfor,
                'phone'         => $phone,
                'cardList'      => empty($cardList) ? '' : $cardList,
                'css_version'   => config::get('mg.css_version'),
                'js_version'    => config::get('mg.js_version'),
                'cdn_url'       => config::get('mg.cdn_url'),
                'host'          => Config::get('mg.host')
            ));
    }

    // 支付
    public function ajaxWxPay(Request $request, Application $app){
        $data = $request->all();
        Log::debug('ajaxWxPay[request data:'.json_encode($data).']');

        $mallService = new MallService();
        $total_price = 0;
        $retail_price = 0;
        $detail = null;
        $vmid = $data['vmid'];
        $products = null;

        foreach ($data['products'] as $key => $value) {
            // 检查商品售价是否与服务器价格一致
            $detail = $mallService->getProDetail($value['pid'], $vmid, 'book');
            Log::debug('ajaxWxPay[productDetail:'.json_encode($detail).']');
            // array('product_id'=>1,product_name='鲜奶',original_price=>100,retail_price=>100,'num'=>2)
            if($detail){
                $products[] = array(
                    'product_id'    => $detail->product_id,
                    'product_name'  => $detail->product_name,
                    'original_price'=> $detail->original_price,
                    'retail_price'  => $detail->retail_price,
                    'num'           => $value['count']
                );
            }
            
            $total_price = $total_price + $detail->retail_price * $value['count'];
        }

        Log::debug('ajaxWxPay[totalPrice:'.$total_price.'][retailPrice:'.$retail_price.']');

        $canNotUseReason = '';
        if(!$data['card_id']){
            $retail_price = $total_price;
        }else{
            $cardId     = $data['card_id'];
            $cardCode  = $data['card_code'];
            $retail_price = $total_price;

            $access_token = $app->access_token;
            $access_token = $access_token->getToken();
            $cardDetail = CouponService::getCardDetail($cardId, $access_token);
            Log::debug('ajaxWxPay[cardDetail:'.json_encode($cardDetail).']');

            $checkCardCode = CouponService::getCardCodeDetail($cardId, $cardCode, $access_token);
            Log::debug('ajaxWxPay[checkCardCode:'.json_encode($checkCardCode).']');

            if($cardDetail['card']['card_type'] === 'CASH' && $checkCardCode['can_consume']){
                $leastCost  = $cardDetail['card']['cash']['least_cost'];
                $reduceCost = $cardDetail['card']['cash']['reduce_cost'];
                if($total_price >= $leastCost){
                    $retail_price = $total_price - $reduceCost;
                }
            }else{
                if($cardDetail['card']['card_type'] != 'CASH'){
                    $canNotUseReason = '非代金券';
                }else if(!$checkCardCode['can_consume']){
                    $canNotUseReason = '卡券状态异常';
                }else{
                    $canNotUseReason = '未知';
                }
            }
            
        }
        Log::debug('ajaxWxPay[totalPrice:'.$total_price.'][retailPrice:'.$retail_price.']');

        //渠道：1扫码预定，2微信商城预定，3扫码购买，4微信商城购买
        $intention = array(
                'channel'       => $request->session()->get('accessChannel'),
                'vmid'          => $data['vmid'],
                'products'      => $products,
                'total_price'   => $total_price,
                'retail_price'  => $retail_price,
                'card_id'       => $data['card_id'],
                'card_name'     => $data['card_name'].($canNotUseReason ? '|'.$canNotUseReason : ''),
                'card_code'     => $data['card_code'],
                'type'          => $data['type'],
                'rate'          => $data['rate'],
                'phone'         => $data['phone']
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
                'wx_id'         => $orderDetail->wx_id,
                'phone'         => $orderDetail->phone
            );

        $count = 0;
        $products = array();
        foreach ($orderDetail->products as $key => $value) {
            $products[$value->product_id] = array(
                'id'    => $value->product_id,
                'pname' => $value->product_name,
                'num'   => $value->num,
                'volume' => $value->volume.($value->unit == 1 ? 'ml' : 'g'),
                'pic'   => $value->img_url
            );
            $count = $count + $value->num;
        }

        $order['products'] = $products;
        $order['count'] = $count;

        return view('wx.result', array(
                'vmInfor'   => $vmInfor,
                'order'     => $order
            ));
    }

    // 我的订单
    public function myorders(Request $request){
        $mallService    = new MallService();
        $apiService     = new ApiService(); 
        $wxId           = $request->session()->get('wxId');
        $orders = $apiService->getReserveOrdersByWxId($wxId);
        Log::debug('myorders[orders:'.json_encode($orders).']');

        return view('wx.myorders', array(
                'orders' => $orders
            ));
    }

    // 订单详情
    public function orderDetails(Request $request, $orderId){
        $mallService    = new MallService();
        $apiService     = new ApiService(); 
        $wxId           = $request->session()->get('wxId');

        $order          = $apiService->getOrdersById($orderId);
        return view('wx.orderDetails', array(
                'order'     => $order
            ));

    }

    // 历史订单
    public function historyOrders(Request $request){
        $wxId           = $request->session()->get('wxId');
        $apiService     = new ApiService();
        $historyOrders  = $apiService->getHistoryOrders($wxId);
        // TODO
        // 初始加载10条，拖动加载更多，再加载10条
        return view('wx.history', array(
                'orders'    => $historyOrders
            ));
    }

    // 获取可以选择的日期
    public function ajaxGetDates(Request $request){
        $apiService = new ApiService(); 
        $num        = $request->input('days');
        $days       = $apiService->getDaysArray($num);
        return json_encode($days);
    }

    // 保存订单停送起始日期
    public function ajaxStopDate(Request $request){
        $startDate  = $request->input('startDate');
        $orderId    = $request->input('orderId');
        $result     = OrderStop::saveOrderStop($orderId, $startDate);
        Log::debug('暂停配送[result: '.json_encode($result).']');
        return json_encode($result);
    }

    // 更新订单停送结束日期
    public function ajaxUpdateDate(Request $request){
        $orderId    = $request->input('orderId');
        $result     = OrderStop::updateOrderStop($orderId);
        Log::debug('ajaxUpdateDate[result:'.$result.']');
        return json_encode($result);
    }
}

