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
use App\Model\OrderStop;

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
        return OrderDetail::createOrderDetails($arr);
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
        $order = self::getOrderDetails($order);
        return $order;
    }

    //获取进行中预定商品列表
    public static function getReserveOrdersByWxId($wxId){
        $orders = Order::getValidateReserveOrders($wxId);
        $stop = OrderStop::getOrderStop(2);
        foreach($orders as &$o){
            $o = self::getOrderDetails($o);
            var_dump($o);exit;
            $o->stop = OrderStop::getOrderStop($o->id);//暂停配送
//            $o->end_date = self::getOrderTime($o)['end_date'];
        }
        return $orders;
    }

    //

    public static function getOrderDetails($order){
        $order->products = OrderDetail::getOrderProducts($order->id);//订单商品详情
        $order->vms = Vm::getVm($order->vmid);//售货机详情
        $order->user = User::getUserByWxId($order->wx_id);//用户
        return $order;
    }

    /**
     * 获取订单配送时间
     * @param $order
     * @return array
     */
    /*
    public static function getOrderTime($order){
        $start_date = $order->start_date;//配送开始时间
        $end_date   = $order->start_date;//配送截止时间
        if($order->channel != 1 && $order->channel !=2 ){//寄卖
            return array(
                'start_date'=> $start_date,
                'end_date'  => $end_date,
            );
        }
        //根据配送频次判断配送截至日期
        $type = $order->type;//时长：30/60/90
        $stop = $order->stop;//订单暂停配送时间数据
        $end_date = self::getOrderEndDate($order->rate,$stop);//暂停天数
        return array(
            'start_date'=> $start_date,
            'end_date'  => $end_date,
        );
    }

    //订单暂停天数
    public static function getOrderEndDate($rate,$order){
        $start_date = $order->state_date;
        $end_date = $start_date;
        $stop = $order->stop;
        $stop_day = 0;//暂停天数
        $interval_day = $order->type - 1;//时长间隔：30/60/90
        if($order->stop){
            $stop_day = (strtotime($stop->end_date)-strtotime($stop->start_time))/86400 + 1;
        }
        echo 'stop_day='.$stop_day.' rate='.$rate;
        if($rate == 0){
            $interval_day += $stop_day;
            $end_date = date('Y-m-d',strtotime('+'.$interval_day.' day',strtotime($start_date)));//配送截止日期
            echo 'ssss='.$end_date;
        }elseif($rate == 1){
            echo 'y';
            //暂停后总配送工作日天数
            if($order->stop){
                for($i = 0;$i <= $stop_day;$i++){
                    $w = date('w',strtotime('+'.$i.' day',strtotime($stop->start_date)));
                    if($w != 0 && $w != 6){
                        $interval_day++;
                    }
                }
            }
            //最后配送日期计算
            for($i = 1; $i <= $interval_day; $i++){
                echo 'hhh='.$i;
                $w = date('w',strtotime('+'.$i.' day',strtotime($start_date)));
                if($w == 0 || $w ==6){
                    $interval_day++;
                    continue;
                }
                $end_date = date('Y-m-d',strtotime('+'.$i.' day',strtotime($start_date)));
                echo '('.$end_date.')';
            }
            $end_date;
        }
        return $end_date;
    }
    */

}