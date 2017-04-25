<?php
namespace App\Service;

use App\Model\Orders;
use App\Model\OrderLogs;
use App\Model\OrderStops; 
use App\Model\Skus; 
use App\Lib\Bussiness;
use DB; 
use Cache; 
use Log;
class MallService{
	// 拉取售货机列表 参数:无
	public function getVmList(){
		$vms = DB::table('vms')
					->join('nodes','vms.node_id','=','nodes.id')
                    ->select('vms.id','vms.vmid','vms.vm_name','nodes.address')
                    ->get();

        return $vms;
	}

	// 根据vmid 拉取所有商品
	// 参数:vmId 售货机id
    public function showPros($vmId){ 
        $proLists = Skus::getAllPros($vmId);
        $exps = DB::table('products')->get();
        $tags = DB::table('tags')->get();
        // 放入缓存
        foreach ($proLists as $k=>$pro) {
        	foreach ($tags as $tag) {
        		if($pro['tag_id'] == $tag->id){
        			$proLists[$k]['tag_name'] = $tag->tag_name;
        		}
        	}

            foreach ($exps as $exp) { //拼接商品生存期
                if($pro['product_id'] == $exp->id){
                    $proLists[$k]['exp'] = $exp->exp;
                }
            }
            Cache::put('PRO_DETAIL_'.$proLists[$k]['product_id'].'_'.$vmId,$proLists[$k],1440);
        }
        return $proLists;
    }

    // 获取某售货机下商品详情
    // 参数1.pid 商品id 2.vmId
    public function getProDetail($pid , $vmId){
        if(empty($pid) || empty($vmId)){
            return 'error';
        }
        $proDetail = Cache::get('PRO_DETAIL_'.$pid.'_'.$vmId);
        if(empty($proDetail)){ //需要重新拉取售货机商品列表,放入缓存
           $list = $this->showPros($vmId);
           $proDetail = Cache::get('PRO_DETAIL_'.$pid.'_'.$vmId);
        }
        if(!empty($proDetail)){
            return $proDetail;
        }else{
            return 'error';
        }
    }

    // 即卖生成单个取货码(下单后操作)
    public function singleBuyCode($order_id,$order_detail_id,$product_id,$vmId){ // 预下单后买码
        $blno = $this->createBlno();
        $date = date('Y-m-d');
        // 去重
        $exists = OrderLogs::where('vmid',$vmId)->where('create_date',$date)->pluck('blno')->toArray();
        // dd($exists);
        while(in_array($blno,$exists)){
            $blno = $this->createBlno();
        }

        $model = new OrderLogs();
        $model->order_id = $order_id;
        $model->order_detail_id = $order_detail_id;
        $model->product_id = $product_id;
        $model->create_date = $date;
        $model->order_status = 201;
        $model->is_reserved = 0;
        $model->blno = $blno;
        $model->vmid = $vmId;
        $res = $model->save();
        Log::info('single_buy_code successfully---returns::'.'order_id:'.$order_id.'order_detail_id:'.$order_detail_id.'product_id:'.$product_id.'blno:'.$blno);
    }

    // 生成8位取货码
    public function createBlno($count=1){
        $timeStr = time();
        if($count>1){ //批量生成
            $blnoArr = [];
            for ($i=0; $i < $count; $i++) { 
                $str = str_shuffle(substr($timeStr,-8));
                if(in_array($str, $blnoArr)){
                    $i--;
                    continue;
                }
                $blnoArr[] = $str;
            }
            return $blnoArr;
        }else{ //生成单个
            $str = str_shuffle(substr($timeStr,-8));
            return $str;
        }
        
    }
}
?>