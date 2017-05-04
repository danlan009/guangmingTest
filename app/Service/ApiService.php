<?php
/**
 * Created by PhpStorm.
 * User: liujinrong
 * Date: 17/4/28
 * Time: 下午3:08
 */

namespace App\Service;


use App\Model\Order;
use App\Model\OrderDetail;
use App\Model\SkuSupply;
use App\Model\User;
use App\Model\Vm;

class ApiService {
    //创建主订单
    public function createOrder($wxId,$channel,$payType=1,$totalPrice,$retailPrice,$cardId,$cardName,$vmid,$type,$rate){
        $order				= new Order();
        $order->wx_id   	= $wxId;
        $order->channel	    = $channel;
        $order->pay_type    = $payType;
        $order->order_status= 1;
        $order->pay_status	= 0;
        $order->total_price	= $totalPrice;
        $order->retail_price= $retailPrice;
        $order->card_id     = $cardId;
        $order->card_name   = $cardName;//微信支付
        $order->vmid        = $vmid;
        $order->start_date	= in_array($channel,array(1,2)) ? date('Y-m-d') : date('Y-m-d',strtotime('+1 day'));
        $order->type        = $type;
        $order->rate        = $rate;
        $order->created_at  = date('Y-m-d H:i:s');
        $order->save();
        return $order;
    }
    //创建订单详情
    public function createOrderDetails($orderId,$products){
        $arr = array();
        if($products && count($products) > 1){//预定
            foreach($products as $p){
                $num = $p['num'];
                for($i = 0 ; $i < $num ; $i++){
                    $arr[] = array(
                        'order_id' => $orderId,
                        'product_id' => $p['product_id'],
                        'product_name' => $p['product_name'],
                        'original_price' => $p['original_price'],
                        'retail_price' => $p['retail_price']
                    );
                }
            }
        }
        return (new OrderDetail())->createOrderDetails($arr);
    }

    //更新货道商品状态
    public static function updateSkuSupplyStatus($vmid,$pId){
        $skuSupply = SkuSupply::getSkuSupply($vmid,$pId);
        if(!$skuSupply){
            return false;
        }
        $ss = SkuSupply::find($skuSupply->id);
        $ss->status = 1;
        $ss->updated_at = date('Y-m-d H:i:s');
        return $ss->save();
    }

    //根据order_id获取订单信息
    public static function getOrderById($orderId){
        $order = Order::find($orderId);
        if(!$order){
            return false;
        }
        $order->products = OrderDetail::getOrderProducts($orderId);//订单商品详情
        $order->vms = Vm::getVm($order->vmid);//售货机详情
        $order->user = User::getUserByWxId($order->wx_id);//用户
        return $order;
    }

}