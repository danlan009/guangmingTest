<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;
class Sku extends Model
{
	protected $table = 'sku'; 
    public static function getAllPros($vmId){
    	/**
    	$proList = DB::table('sku')
    					->join('tags','sku.tag_id','=','tags.id')
    					->where('vm_id',$vmId)
		    			->select('sku.product_id','sku.product_name','sku.original_price','sku.retail_price','sku.tag_id','tags.tag_name')
		    			->groupBy('product_id')
		    			->get();
		**/

		$proList = DB::table('sku')
    					->where('vm_id',$vmId)
		    			->select('product_id','product_name','original_price','retail_price','tag_id')
		    			->groupBy('product_id')
		    			->get();
	
		$proInfos = DB::table('products')->get();
		$tags = DB::table('tags')->get();
		foreach ($proList as $k => $pro) {
			// 添加tag_name信息
			foreach ($tags as $tag) {
				if($pro->tag_id == $tag->id){
					$proList[$k]->tag_name = $tag->tag_name;
				}
			}

			// 添加exp 和 volume信息
			foreach ($proInfos as $proInfo) { //拼接商品详细信息
                if($pro->product_id == $proInfo->id){
                    $proList[$k]->exp = $proInfo->exp;
                    $unit = ($proInfo->unit==1)?'ml':'g';
                    $proList[$k]->volume = ((string)$proInfo->volume).$unit; //拼接容量
                    // $proList[$k]->des = $proInfo->des; //拼接描述文案
                    $proList[$k]->des = ''; //拼接描述文案
                }
            }

		}
		return $proList;
    }

}
