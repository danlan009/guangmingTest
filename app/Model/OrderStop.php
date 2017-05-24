<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use DB;

class OrderStop extends Model
{
    protected $table = 'order_stop';


    //查询订单暂停配送时间
    public static function getOrderStop($orderId){
        return OrderStop::where('order_id',$orderId)->get();
    }

    public static function saveOrderStop($orderid,$start_date){
        if($orderid&&$start_date){
            $order_id = $orderid;
            $date = $start_date;
            $wx_id = session('wxId');

            return DB::table('order_stop')->insert(
                ['order_id'=>$order_id,'start_date'=>$date,'wx_id'=>$wx_id,'created_at'=>date('Y-m-d H:i:s')]
            );

        }else{
            return [];
        }

    }

    public static function updateOrderStop($orderId){
        //先取出第一個
        $orderinfo = DB::table('orders')->where('id',$orderId)->first();
        $order_stop = DB::table('order_stop')->where('order_id',$orderId)->orderBy('id','desc')->first();
        $id = $order_stop->id;
        $start_date = $order_stop->start_date;
        $end_date = date('Y-m-d');
        $day = ceil( (strtotime($end_date) - strtotime($start_date)) / 86400);
        if($orderinfo->rate==0){
             return DB::table('order_stop')
                ->where('id',$id)
                ->update(['end_date'=>$end_date,'stop_day'=>$day]);

        }else{
            $k=0;
            for($i=0;$i<$day;$i++){
                $a = date('w',strtotime("-$i days"));
                if($a=="0"||$a=="6"){

                }else{
                    $k++;
                }
            }

             return DB::table('order_stop')
                ->where('id',$id)
                ->update(['end_date'=>$end_date,'stop_day'=>$k]);

        }

    }

}
