<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;

class OrderStop extends Model
{
    protected $table = 'order_stop';

    //查询订单暂停配送时间
    public static function getOrderStop($orderId){
        return OrderStop::where('order_id',$orderId)->first();
    }
}
