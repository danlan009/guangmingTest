<?php
namespace App\Service;
use DB;
class OrderService{
	//获取某台售货机当天需要买码的订单,
    	//包括正常预定订单,和之前未取且交占道费的订单
    public static function getDailyOrders($vmId){
    	//正常日配送订单 
    	$dailyOrders = array();   
 
    	$normalOrders = DB::table('orders')
    						->join('order_details as od','orders.id','=','od.order_id')
                            ->where('orders.vmid',$vmId)
                            ->where('orders.order_status',2) //配送中
    						->select('orders.vmid','od.id as order_detail_id','od.order_id','od.product_id','od.product_name','od.original_price','od.retail_price')
    						->get(); 
    						// ->toArray();
    	// dd($normalOrders);
    	$dailyOrders['normalOrders'] = $normalOrders;
    	//获取占道订单
    	$reservedOrders = DB::table('order_logs')
                            ->where('vmid',$vmId)
    						->where('order_status',201)
    						->where('is_reserved',2) 
    						->select('vmid','order_id','order_detail_id','product_id')
    						->get();
    						// ->toArray();
    	$dailyOrders['reservedOrders'] = $reservedOrders;
        // dd($dailyOrders);
    	return $dailyOrders;
    	
    }

    // 二次补货获取漏补订单
    public function getLeftOrders(){

    }
    // 正常每日预定/占道订单/即卖订单
    public function getPickupRes($orderId){ 
        $status = OrderLogs::select('order_status')->where('order_id',$orderId);
        return $status;
    }

    public function getCodeFailOrds($vmId){
        $list = OrderLogs::where('blno','')
                            ->where('vmid',$vmId)
                            ->get()
                            ->toArray();
        return json_encode($list);
    }

    public function getDeliverFailOrd($vmId){ 
        $list = OrderLogs::where('order_status',400)
                            ->where('vmid',$vmId)
                            ->get()
                            ->toArray();
        return json_encode($list);
    }

    // 补货时,按product_id 分组统计 订单,方便随机分配订单
    public static function handleOrdersToAllot($vmId){
        $dailyOrders = OrderService::getDailyOrders($vmId)['normalOrders'];
        $formatOrders = [];
        foreach ($dailyOrders as $k => $order) {

            $formatOrders[$order->product_id][] = [
                                                    'order_id' => $order->order_id,
                                                    'order_detail_id' => $order->order_detail_id,
                                                  ];
        }
        return $formatOrders;
    }
}
?>