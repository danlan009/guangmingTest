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
use App\Model\OrderLog;
use App\Model\SkuSupply;
use App\Model\User;
use App\Model\Vm;
use App\Model\OrderStop;
use Log;

class ApiService {
    //创建主订单
    public function createOrder($wxId,$channel,$payType=1,$totalPrice,$retailPrice,$cardId,$cardName,$cardCode,$vmid,$type,$rate,$phone){
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
        $order->card_code   = $cardCode;
        $order->vmid        = $vmid;
        $order->start_date	= in_array($channel,array(1,2)) ? date('Y-m-d') : date('Y-m-d',strtotime('+1 day'));
        $order->type        = $type;
        $order->rate        = $rate;
        $order->phone       = $phone;
        $order->created_at  = date('Y-m-d H:i:s');
        $order->save();
        return $order;
    }
    //创建订单详情
    public function createOrderDetails($orderId,$products){
        Log::debug('createOrderDetails[orderId:'.$orderId.'][products:'.json_encode($products).']');
        $arr = array();
        if($products && count($products) >= 1){//预定
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

    /*
     * 用户订单列表合并（进行中预定列表和已完成订单）
     */
    public static function getOrdersByWxId($wxId){
        $orders = Order::getOrders($wxId);
        $historyOrders = array();
        $unFinishedOrders = array();
        foreach($orders as &$o){
            $o = self::getOrderDetails($o);
            if($o->order_status == 3){//已完成订单
                $historyOrders[] = $o;
            }else{//预定未配送完成订单
                $unFinishedOrders = $o;
            }
        }
        return array(
            'unFinishedOrders' => $unFinishedOrders,
            'historyOrders'    => $historyOrders
        );
    }

    //获取进行中预定商品列表
    public static function getReserveOrdersByWxId($wxId){
        $orders = Order::getValidateReserveOrders($wxId);
        foreach($orders as &$o){
            $o = self::getOrderDetails($o);
        }
        return $orders;
    }

    //已完成订单列表
    public static function getHistoryOrders($wxId){
        $orders = Order::getHistoryOrders($wxId);
        foreach($orders as &$o){
            $o = self::getOrderDetails($o);
        }
        return $orders;
    }

    /**
     * 根据订单ID获取预定订单详情，包括该订单每天各个商品的出货情况
     * produts = array(
        'details' => array([],[],[]),
     *  'date'    => '2017.05.17(周三)'
     * )
     * @param $orderId
     * @return mixed
     */
    public static function getOrdersById($orderId){
        $order = Order::find($orderId);
        //获取订单日志
        $products = array();
        $datas = OrderLog::getOrdersAndStatus($orderId);
        foreach($datas as &$d){
            $d->img_url = StatService::getImg('products',$d->id,'l');//标准图片
            $key = $d->create_date;
            $products[$key]['details'][] = $d;
            $products[$key]['date'] = date('Y.m.d',strtotime($key)).'('.self::getChinaWeek($key).')';
        }
        $order->products = $products;
        $order = self::getOrderDetails($order);
        return $order;
    }

    public static function getOrderDetails($order){
        if(!isset($order->products)){
            //获取商品并查询图片
            $products = OrderDetail::getOrderProducts($order->id);//订单商品详情
            foreach($products as &$p){
                $p->img_url = StatService::getImg('products',$p->product_id,'l');//标准图片
            }
            $order->products = $products;
        }
        $order->vms = Vm::getVm($order->vmid);//售货机详情
        $order->user = User::getUserByWxId($order->wx_id);//用户
        if($order->channel == 1 || $order->channel == 2){//预定
            $order->stop = OrderStop::getOrderStop($order->id);//暂停配送
            $order->other_date = OrderService::getDispatchingDate($order->id);
        }
        return $order;
    }

    /*
     * 明天后n天数组信息，格式：2017-05-18 周四
     */
    public static function getDaysArray($days){
        $dayArr = array();
        for($i = 1; $i <= $days; $i++){
            $date = date("Y-m-d",strtotime("+$i days",time()));
            $week = self::getChinaWeek($date);
            $dayArr[] = "$date $week";
        }
        return $dayArr;
    }

    public static function getChinaWeek($date){
        $week = date('w',strtotime($date));
        $str  = '';
        switch($week){
            case 1 : $str = '周一'; break;
            case 2 : $str = '周二'; break;
            case 3 : $str = '周三'; break;
            case 4 : $str = '周四'; break;
            case 5 : $str = '周五'; break;
            case 6 : $str = '周六'; break;
            case 0 : $str = '周日'; break;
            default: break;
        }
        return $str;
    }

}