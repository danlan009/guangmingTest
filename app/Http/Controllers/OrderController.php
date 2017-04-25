<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller; 

use App\Model\OrderLogs;
use DB;
class OrderController extends Controller
{
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
