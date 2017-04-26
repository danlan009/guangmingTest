<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;
class Skus extends Model
{
	protected $table = 'skus';
    public static function getAllPros($vmId){
    	/**
    	$proList = DB::table('skus')
    					->join('tags','skus.tag_id','=','tags.id')
    					->where('vm_id',$vmId)
		    			->select('skus.product_id','skus.product_name','skus.original_price','skus.retail_price','skus.tag_id','tags.tag_name')
		    			->groupBy('product_id')
		    			->get();
		**/

		$proList = DB::table('skus')
    					->where('vm_id',$vmId)
		    			->select('product_id','product_name','original_price','retail_price','tag_id')
		    			->groupBy('product_id')
		    			->get();

		$tags = DB::table('tags')->get();
		foreach ($proList as $k => $pro) {
			foreach ($tags as $tag) {
				if($pro->tag_id == $tag->id){
					$proList[$k]->tag_name = $tag->tag_name;
				}
			}
		}
		return $proList;
    }
}
