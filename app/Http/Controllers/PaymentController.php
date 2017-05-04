<?php

namespace App\Http\Controllers;

use App\Model\Order;
use App\Model\OrderDetail;
use App\Model\OrderLog;
use App\Model\SkuSupply;
use App\Model\User;
use App\Model\WxTrade;
use App\Service\ApiService;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use EasyWeChat\Foundation\Application;
use EasyWeChat\Payment\Order as wechatOrder;
class PaymentController extends Controller
{
    /**
     * 预下单
     * @param Request $request
     * @param Application $wechat
     * intention:['vmid'=>'0081801','channel'=>'1',
     *            'total_price'=>'100','retail_price'=>'100',
     *            'products'=>array(
     *                        array('product_id'=>1,product_name='鲜奶',original_price=>100,retail_price=>100,'num'=>2),
     *                        array('product_id'=>2,product_name='酸奶',original_price=>100,retail_price=>100,'num'=>2),
     *             ),
     *            'card_id'='1111','card_name'='满10减5元','type'=>30,'rate'=>0,phone='15612345678'],
     */
    public function ajaxPrepay(Request $request, Application $app){
        //微信权限
        $oauthUser = session('wechat.oauth_user');
        $intention = $request->session()->get('intention');
        $rt = array();
        Log::debug('ajaxPrepay:[intention:'.json_encode($intention).']');
        if(empty($intention)){
            $rt = [
                'code'  => 400,
                'msg'   => '页面访问错误'
            ];
        }else{//预下单
            $wxId  = $request->session()->get('wxId');
            $channel = $intention['channel'];
            $vmid    = $intention['vmid'];
            $products = $intention['products'];
            $o_total_price = $intention['total_price'];//总实际价格
            $o_retail_price = $intention['retail_price'];//总支付价格
            $cardId  = $intention['card_id'];
            $cardName= $intention['card_name'];
            $type    = $intention['type'];
            $rate    = $intention['rate'];
            //添加手机号
            if(isset($intention['phone']) && !empty($intention['phone'])){
                User::addPhone($wxId,$intention['phone']);
            }
            //TODO: 创建订单
            Log::debug('PaymentController-ajaxPrepay product='.json_encode($products));
            //创建主订单
            $apiService = new ApiService();
            $order   = $apiService->createOrder($wxId,$channel,1,$o_total_price,$o_retail_price,$cardId,$cardName,$vmid,$type,$rate);
            $orderId = $order->id;
            //创建订单详情
            $apiService->createOrderDetails($orderId,$products);
            //建立微信交易单
            $wxTrade = new WxTrade();
            $wxTrade->order_id = $orderId;
            $wxTrade->channel = $channel;
            $wxTrade->total_fee = $o_retail_price;
            $wxTrade->vmid = $vmid;
            $wxTrade->created_at = date('Y-m-d H:i:s');
            $wxTrade->save();
            //微信支付
            $payment = $app->payment;
            $attrs = [
                'trade_type'       => 'JSAPI', // JSAPI，NATIVE，APP...
                'body'             => $channel.'-'.$vmid,
                'detail'           => '光明订奶',
                'out_trade_no'     => strval(time()).'_'.strval($wxTrade->id),
                'total_fee'        => $o_retail_price,
                'notify_url'       => config('wechat.payment')['notify_url'],
                'openid'       => $oauthUser->getId(),
                'appid'        => config('wechat.app_id')
            ];
            Log::debug('before wx prepay', ['attrs'=>$attrs]);
            $result = $payment->prepare(new wechatOrder($attrs));
            Log::debug("wx prepay", ['result'=>$result]);
            if ($result->return_code == 'SUCCESS' && $result->result_code == 'SUCCESS'){
                $prepayId = $result->prepay_id;
                Log::debug("prepay id: $prepayId");
                $config = $payment->configForJSSDKPayment($prepayId);
                $rt = [
                    'code'      => 200,
                    'config'    => $config,
                    'wxTxnId'   => $wxTrade->id,
                ];
            }else{
                $rt = [
                    'code'      => 500,
                ];
            }

        }
        return json_encode($rt);
    }

    /**微信支付回调
     * @param Application $wechat
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \EasyWeChat\Core\Exceptions\FaultException
     */
    public function notifyPayment(Application $app)
    {
        $response = $app->payment->handleNotify(function($notify, $successful){
            Log::debug("wechat payment notify", ['notify'=>$notify, 'successful'=>$successful]);
            $wxTradeId = substr($notify->out_trade_no, 11);
            $wxTrade = WxTrade::find($wxTradeId);

            //检查回调是否已经接受并执行
            if(!empty($wxTrade->result_code)){
                Log::debug('notifyPayment[successed]');
                return 'SUCCESS';
            }
            $wxTrade->openid  = $notify->openid;
            $wxTrade->transaction_id = $notify->transaction_id;
            $wxTrade->trade_type = $notify->trade_type;
            $wxTrade->result_code = $notify->result_code;
            $wxTrade->return_code = $notify->return_code;
            if(property_exists($notify, 'return_msg')){
                $wxTrade->return_msg = $notify->return_msg;
            }
            $wxTrade->is_subscribe = $notify->is_subscribe;
            $wxTrade->time_end = $notify->time_end;
            $wxTrade->updated_at = date('Y-m-d H:i:s');
            $wxTrade->save();
            //查询订单
            $order = Order::find($wxTrade->order_id);
            if($notify->result_code=='SUCCESS'){
                Log::debug('支付成功');
                //修改订单支付状态
                $order->pay_status = 1;//支付成功
                $order->pay_time = date('Y-m-d H:i:s');
                //纪录订单order_logs
                $orderDetails = OrderDetail::getOrderDetails($wxTrade->order_id);
                $orderLogs = array();
                //现场购买通知出货
                if(in_array($wxTrade->channel,[3,4])){
                    $orderLogs = array(
                        'order_id'        => $wxTrade->order_id,
                        'order_detail_id' => $orderDetails[0]->id,
                        'product_id'      => $orderDetails[0]->product_id,
                        'create_date'     => date("Y-m-d H:i:s"),
                        'pickup_date'     => date('Y-m-d H:i:s'),
                        'is_reserved'     => 0,
                        'vmid'            => $wxTrade->vmid
                    );
                    Log::debug('现场购买');
                    //TODO:调用java server出货接口
                    if(true){//出货成功
                        //将订单状态改成配送完成
                        $order->order_status = 3;
                        //订单日志纪录出货成功
                        $orderLogs['order_status'] = 200;
                    }else{//出货失败
                        $orderLogs['order_status'] = 400;
                    }
                    //更新货道补货信息，出货成功－状态置空
                    ApiService::updateSkuSupplyStatus($wxTrade->vmid,$wxTrade->product_id);
                }else{
                    //todo:买码并下发给服务器－调用买码接口

                    //预定order_logs待取货状态
                    foreach($orderDetails as $od){
                        $orderLogs[] = array(
                            'order_id'        => $wxTrade->order_id,
                            'order_detail_id' => $od->id,
                            'product_id'      => $od->product_id,
                            'create_date'     => date('Y-m-d',strtotime('+1 day')),
                            'is_reserved'     => 0,
                            'vmid'            => $wxTrade->vmid,
                            'order_status'    => 201
                        );
                    }
                }
                OrderLog::createOrderLogs($orderLogs);
            }else{
                $order->pay_status = 2;//支付失败
                $order->pay_time = date('Y-m-d H:i:s');
                Log::error("wechat payment fail", ['notify'=>$notify]);
            }
            $s = $order->save();
            return $s;
        });
        return $response->send();
    }

    public function test(){
        $data = ApiService::getOrderById(1);
        var_dump($data);
        exit;
    }
}
