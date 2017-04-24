<?php  
namespace App\Lib;
use DB;
class Bussiness{
	 //获取某台售货机当天需要买码的订单,
    	//包括正常预定订单,和之前未取且交占道费的订单
    public static function getDailyOrders($vmId){
    	// orders
    	// $orderModel = new Orders();
    	//正常日配送订单
    	// $normalOrders = $orderModel
    	// 					->where('orders_status',2)
    	// 					->get()
    	// 					->toArray();
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

    public static function getInfoFromJson(Request $request){
        $type = $request->input('type');
        $data = '';
        switch ($type) {
            case 'deliverymen':
                $data = file_get_contents(env('SENDER_FILE_PATH'));
                Log::info('get senderlist from json---'.$data);
                break;
            case 'vms':
                $data = file_get_contents(env('VMS_FILE_PATH'));
                Log::info('get senderlist from json---'.$data);
                break; 
            case 'productsExp':
                $data = file_get_contents(env('PRODUCTSEXP_FILE_PATH'));
                Log::info('get senderlist from json---'.$data);
                break;
        }
        return json_decode($data,true); //返回数组格式数据
        
    }

    // 生成8位取货码
    public static function createBlno($timeStr){
        
    }
}
?>