<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Skus extends Model
{
	protected $table = 'skus';
    public static function getAllPros($vmId){
    	$proList = Skus::where('vm_id',$vmId)
		    			->select('product_id','product_name','tag_id','original_price','retail_price')
		    			->groupBy('product_id')
		    			->get()
		    			->toArray();
		return $proList;
    }
}
