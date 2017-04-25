<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;
class Skus extends Model
{
	protected $table = 'skus';
    public static function getAllPros($vmId){
    	$proList = DB::table('skus')
    					->join('tags','skus.tag_id','=','tags.id')
    					->where('vm_id',$vmId)
		    			->select('skus.product_id','skus.product_name','skus.original_price','skus.retail_price','skus.tag_id','tags.tag_name')
		    			->groupBy('product_id')
		    			->get();


		return $proList;
    }
}
