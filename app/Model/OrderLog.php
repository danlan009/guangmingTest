<?php

namespace App\Model;
use DB;
use Illuminate\Database\Eloquent\Model;

class OrderLog extends Model
{
    public $timestamps = false;
    protected $guarded = [];


    //批量插入
    public static function createOrderLogs($array){
        return DB::table('order_logs')->insert($array);
    }

    //查看补单成功的用户和商品信息
    public static function querySuccessOrderLogs(){
        $order_detail_id = DB::table('order_logs')
            ->where('create_date',date('Y-m-d'))
            ->where('order_status',201)
            ->lists('order_detail_id');
//        $order_detail_id = DB::table('order_logs')
//            ->where('create_date','2017-04-25')
//            ->where('order_status',201)
//            ->lists('order_detail_id');
        if (count($order_detail_id)>0){
            return DB::table('orders')
                ->join('order_details','orders.id','=','order_details.order_id')
                ->select('order_details.product_name','orders.wx_id')
                ->whereIn('order_details.id',$order_detail_id)
                ->orderBy('orders.wx_id','desc')
                ->get();
        }else{
            return [];
        }


    }

    public static function queryTodayFailOrderList(){
        $order_detail_id = DB::table('order_logs')
            ->where('create_date',date('Y-m-d'))
            ->whereNull('pickup_date')->lists('order_detail_id');
        if(count($order_detail_id)>0){
            return DB::table('order_details')
                ->join('orders','orders.id','=','order_details.order_id')
                ->select('orders.wx_id','order_details.*','orders.phone')
                ->whereIn('order_details.id',$order_detail_id)
                ->get();
        }else{
            return [];
        }
    }

    //获取订单的取货结果 主要是正在出货的状态
    public static function getOrderOutResult(){
        return DB::table('order_details')
            ->join('orders','orders.id','=','order_details.order_id')
            ->join('order_logs','order_logs.order_id','=','orders.id')
            ->select('orders.wx_id','order_details.*','order_logs.order_status')
            ->where('order_logs.create_date','=',date('Y-m-d'))
            ->where('order_logs.order_status',203)
            ->get();
    }

    public static function getOrdersAndStatus($orderId){
        return DB::table('order_logs')
            ->join('products','order_logs.product_id','=','products.id')
            ->select('products.*','order_logs.create_date','order_logs.order_status')
            ->where('order_logs.order_id',$orderId)
            ->orderBy('order_logs.id','asc')
            ->get();
    }


    public static function getReservedOrders($vmid){
        return DB::table('order_logs')
                            ->where('vmid',$vmid)
                            ->where('order_status',201)
                            ->where('is_reserved',1) 
                            ->select('vmid','order_id','order_detail_id','product_id')
                            ->get();
    }
    public static function updateOrderStatusByOrderId($orderId,$orderStatus){
        return DB::table('order_logs')
            ->where('order_id',$orderId)
            ->update(['order_status' => $orderStatus]);

    }
}
