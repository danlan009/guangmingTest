<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;

class Order extends Model
{
    //获取订单列表
    public static function getOrders($wxId){
        return Order::where('wx_id',$wxId)
            ->where('pay_status',1)
            ->get();
    }

    //根据用户查询订单
    public static function getValidateReserveOrders($wxId){
        return Order::where('wx_id',$wxId)
            ->where('pay_status',1)
            ->where('order_status','<>',3) //配送未完成
            ->whereIn('channel',[1,2])
            ->orderBy('vmid','asc')
            ->get();
    }

    //获取用户历史订单
    public static function getHistoryOrders($wxId){
        return Order::where('wx_id',$wxId)
            ->where('pay_status',1)
            ->where('order_status',3) //配送未完成
            ->orderBy('vmid','asc')
            ->get();
    }

    // 获取每日正常配送订单(配送中大订单及其所属小订单)
    public function getNormalOrders($vmid){
        return DB::table('orders')
                            ->join('order_details as od','orders.id','=','od.order_id')
                            ->where('orders.vmid',$vmid)
                            ->where('orders.order_status',2) //配送中
                            ->select('orders.vmid','od.id as order_detail_id','od.order_id','od.product_id','od.product_name','od.original_price','od.retail_price')
                            ->get();
    }

    //定时任务修改orders的状态 暂停订单
    public function updateOrderStatusAfterOrderStop(){
        return DB::table('order_stop')->join('orders','orders.id','=','order_stop.order_id')
            ->where('order_stop.start_date',date('Y-m-d'))
            ->where('order_stop.stop_day','>',0)
            ->where('orders.order_status','<>',4)
            ->update(['orders.updated_at'=>date('Y-m-d'),'orders.order_status'=>4]);

    }

    public function updateOrderStatusAfterOrderStopSec(){
        return DB::table('order_stop')->join('orders','orders.id','=','order_stop.order_id')
            ->where('order_stop.end_date',date('Y-m-d',strtotime('-1 day')))
            ->where('order_stop.stop_day','>',0)
            ->update(['orders.updated_at'=>date('Y-m-d'),'orders.order_status'=>2]);
    }


}
