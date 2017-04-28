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
                            ->where('orders.orders_status',2) //配送中
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

    //判断某台售货机某件商品是否可以继续预定
    public function isAbleToBook(Request $request){
        $vmId = $request->input('vmId');
        $productId = $request->input('productId');
        //计算已有预定订单对应商品数量
        $pNum = DB::table('orders')
                    ->rightJoin('orders_detail as od','orders.id','=','od.order_id')
                    ->where('orders.vmid',$vmId)
                    ->where('orders.orders_status',1)
                    ->where('od.product_id',$productId)
                    ->select('orders.vmid','od.product_id')
                    // ->get()
                    ->count();
                    // ->toArray();

        //计算售货机该售货机该商品可用于预定最大值
        $max;
        return $max>$pNum;
    }

    //判断某台售货机某件商品是否可以继续购买
    public function isAbleToBuy(Request $request){
        $vmId = $request->input('vmId');
        $productId = $request->input('productId');
        //计算该售货机该商品上位取货商品数量
        $pNum = DB::table('orders')
    }
}
?>