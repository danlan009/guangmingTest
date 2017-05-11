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
            ->get();
    }

    //获取用户历史订单
    public static function getHistoryOrders($wxId){
        return Order::where('wx_id',$wxId)
            ->where('pay_status',1)
            ->where('order_status',3) //配送未完成
            ->get();
    }
}
