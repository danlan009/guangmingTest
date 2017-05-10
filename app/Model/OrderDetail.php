<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;

class OrderDetail extends Model
{
    //批量插入
    public static function createOrderDetails($array){
       return DB::table('order_details')->insert($array);
    }

    //查询订单详情
    public static function getOrderDetails($orderId){
        return DB::table('order_details')
                ->where('order_id',$orderId)
                ->get();
    }

    //查询订单商品详情
    public static function getOrderProducts($orderId){
        return DB::table('order_details')
                ->select('order_details.*','products.volume','products.unit')
                ->join('products','order_details.product_id','=','products.id')
                ->where('order_details.order_id',$orderId)
                ->get();
    }
}
