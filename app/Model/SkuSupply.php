<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class SkuSupply extends Model
{
    protected $table = 'sku_supplys';
    //查询订单详情
    public static function getSkuSupply($vmid,$pId){
        return \DB::table('sku_supplys')
                ->join('skus','sku_supplys.sku_id','=','skus.id')
                ->select('sku_supplys.*')
                ->where('skus.vmid',$vmid)
                ->where('skus.product_id',$pId)
                ->where('sku_supplys.status','<>',1)
                ->orderBy('sku_supplys.id','asc')
                ->first();
    }
}
