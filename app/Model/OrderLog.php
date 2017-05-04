<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OrderLog extends Model
{
    public $timestamps = false;
    protected $guarded = [];


    //批量插入
    public static function createOrderLogs($array){
        return DB::table('order_logs')->insert($array);
    }

}
