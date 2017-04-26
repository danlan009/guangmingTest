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
	// 参数:1.vmId 售货机id 
    //      2.type 'sale':即卖
    //             'book':预定

    public function showPros($vmid , $type){
        if(!isset($vmid) || !isset($type)){
            return 'parameter missing!';
        }

        $proList = Skus::getAllPros($vmid);
        Log::debug('MallService::getAllPros---'.json_encode($proList));

        // 放入缓存
        foreach ($proList as $k=>$pro) {
            $pid = $pro->product_id;
            if($type == 'sale'){ 
                $num = $this->getNumOfSale($vmid,$pid); //计算即卖商品剩余数量
                $pro->count = $num;      
            }else{
                $num = $this->getNumOfBook($vmid,$pid); //计算预定商品剩余数量
                Log::debug('getNumOfBook---returns---'.$num);
                $pro->count = $num;
            }

            Cache::put('PRO_DETAIL_'.$proList[$k]->product_id.'_'.$vmid,$proList[$k],1440);
        }
        Log::debug('MallService::showPros to '.$type.' put in Cache---'.json_encode($proList));
        return $proList;
    }

    // 获取某售货机下商品详情
    // 参数1.pid 商品id 2.vmId 3.type:'sale/book'
    public function getProDetail($pid,$vmid,$type){
        if(empty($pid) || !isset($vmid) || !isset($type)){
            return 'parameter missing!';
        }
        $proDetail = Cache::get('PRO_DETAIL_'.$pid.'_'.$vmid);
        Log::debug('MallService::getProDetail get data from Cache---'.json_encode($proDetail));
        if(empty($proDetail)){ //需要重新拉取售货机商品列表,放入缓存
           $list = $this->showPros($vmid,$type);
           $proDetail = Cache::get('PRO_DETAIL_'.$pid.'_'.$vmid);
           Log::debug('MallService::getProDetail after showPros get data from Cache---'.json_encode($proDetail));
        }
        if(!empty($proDetail)){
            return $proDetail;
        }else{
            return 'error';
        }
    }

    // 计算某台售货机下即卖商品剩余数量
    public function getNumOfSale($vmid,$product_id){
        $num = DB::table('skus')
                        ->join('sku_supplys as skps','skus.id','=','skps.sku_id')
                        ->where('skus.vm_id',$vmid)
                        ->where('skus.product_id',$product_id)
                        ->where('skps.status',2)
                        ->count();
        return $num;
    }

    // 计算某台售货机预定商品剩余数量
    public function getNumOfBook($vmid,$product_id){
        $skuList = DB::table('skus')
                        ->where('vm_id',$vmid)
                        ->where('product_id',$product_id)

                        ->select('id','sku_size')
                        ->get();
        $max = 0;
        foreach ($skuList as $sku) {
            $max += $sku->sku_size;
        }
        
        $existCount = DB::table('orders')
                        ->join('order_details as ods','orders.id','=','ods.order_id')
                        ->where('orders.channel',1)
                        ->where('orders.orders_status','<>',3)
                        ->where('orders.vmid',$vmid)
                        ->where('ods.product_id',$product_id)
                        ->count();
        // dd($existCount);
        $num = $max - $existCount;
        return $num;
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