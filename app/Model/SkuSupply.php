<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;
class SkuSupply extends Model
{
    protected $table = 'sku_supplys';

    // public $timestamps = false;
    protected $guarded = [];
 
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

    //获取售货机的过期商品
    public static function getExpireProduct(){
        //首先查询所有商品如果时间大于10天则更改状态 更改更新时间
        \DB::update('update sku_supplys set status = 3,update_at = ? where created_at < ?',[date('Y-m-d H:i:s'),date('Y-m-d H:i:s',strtotime('-10 days'))]);

        return \DB::table('sku_supplys')
            ->join('skus','sku_supplys.sku_id','=','skus.id')
            ->select('sku_supplys.*','skus.product_name','skus.vmid','skus.vm_name')
            ->where('sku_supplys.status',3)
            ->get();
        //获取到了过期商品

    }

    // 获取售货机当前存在所有补货记录
    public function getExists($vmid){
        $date = date('Y-m-d');
        $sku_array = DB::table('skus')
                            ->where('vmid',$vmid)
                            ->pluck('id');
        return SkuSupply::whereIn('sku_id',$sku_array)
                            ->whereIn('status',[2,3])
                            ->get();
    }

}
