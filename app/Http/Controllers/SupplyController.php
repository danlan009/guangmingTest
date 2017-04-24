<?php
 
namespace App\Http\Controllers; 

use Illuminate\Http\Request; 
use App\Lib\Bussiness;
use Log; 
use DB; 
class SupplyController extends Controller
{
    public function getSenderList(Request $request){
    	$senderList = file_get_contents(env('SENDER_FILE_PATH'));
    	if($senderList){
            Log::info('get senderlist from json---'.json_encode($senderList));
    		return json_decode($senderList,true);
    	}
    }

    

    public function arriveNotify($orderList){ //接收来自server请求 orderList 应含有补货详情
        // 1.正常每日订单(对比补货详情) 2.当天之前未取货占道订单
        $dailyOrders = Bussiness::getDailyOrders();
        $normalOrders = $dailyOrders['normalOrders']; //所有预定订单
        $arriveOrders = $orderList[''];
        // $
    }

    // 补货前获取售货机内现有商品列表
    public function getNowProList($vmId){
        // $vmId = $request->input("vmId");
        
        $proList = DB::table('skus')
                            ->join('sku_supplys','skus.id','=','sku_supplys.sku_id')
                            ->select('skus.id','skus.seq','skus.product_id','skus.product_name','skus.sku_size','sku_supplys.location','sku_supplys.status')
                            ->where('vm_id',$vmId)
                            ->where('status','<>',1) //未出货
                            ->get();
        // dd($proList);
        return $proList;
    }

    // 补货时计算补货数据
    public function getSupplyData(Request $request){ 
        $vmId = $request->input('vmId');
        // dd($vmId);
        $vmSkuNum = DB::table('vms')
                            ->where('id',$vmId)
                            ->value('sku_num');
                            // ->get();
        // 获取货道配置商品信息
        $skuSet = DB::table('skus')
                            ->where('vm_id',$vmId)
                            ->select('seq','product_name','product_id','sku_size')
                            ->get();
        // 处理信息
        $afterSkuSet = [];
        foreach ($skuSet as $list) {
            $afterSkuSet[$list->seq]['product_id'] = $list->product_id;
            $afterSkuSet[$list->seq]['product_name'] = $list->product_name;
            $afterSkuSet[$list->seq]['sku_size'] = $list->sku_size;
        }
        // dd($afterSkuSet);              
        // dd($vmSkuNum);
        $proList = $this->getNowProList($vmId); //返回售货机内已存在商品列表
        // dd($proList);
        $afterProList = [];
        foreach ($proList as $k => $pro) {
            $afterProList[$pro->seq]['product_id'] = $pro->product_id;
            $afterProList[$pro->seq]['product_name'] = $pro->product_name;
            $afterProList[$pro->seq]['sku_size'] = $pro->sku_size;
            $afterProList[$pro->seq]['normal'] = isset($afterProList[$pro->seq]['normal'])?$afterProList[$pro->seq]['normal']:0;
            $afterProList[$pro->seq]['warn'] = isset($afterProList[$pro->seq]['warn'])?$afterProList[$pro->seq]['warn']:0;
            if($pro->status == 2){
                $afterProList[$pro->seq]['normal']++;
            }else{
                $afterProList[$pro->seq]['warn']++;
            }
        }

        $dailyOrders = Bussiness::getDailyOrders($vmId);
        // 需要配送的商品列表
        $normalOrders = $dailyOrders['normalOrders'];
        // dd($normalOrders);
        // 配送列表按商品分组统计(product_id=>count)
        $countList = []; 
        foreach ($normalOrders as $normalOrder) {
            if(isset($countList[$normalOrder->product_id])){
                $countList[$normalOrder->product_id]++ ;
            }else{
                $countList[$normalOrder->product_id] = 1;
            }
        }
        // dd($countList);
        // dd($afterProList);
        // 计算每一条货道补货信息
        $totalList = [];
        for ($i=1; $i <= $vmSkuNum ; $i++) { 
            // if($countList[$afterProList[$i]['product_id']] = 0)
            if(!empty($afterProList[$i])){
                $totalList[$i]['product_id'] = $afterProList[$i]['product_id'];
                $totalList[$i]['product_name'] = $afterProList[$i]['product_name'];
                $totalList[$i]['warn'] = $afterProList[$i]['warn'];
                $totalList[$i]['normal'] = $afterProList[$i]['normal'];

                //计算需要增加的商品数量
                if(null != $countList[$afterProList[$i]['product_id']]){ //携带补货商品中存在该货道商品
                    $addCount = $afterProList[$i]['sku_size'] - $totalList[$i]['normal']; 
                    if($countList[$afterProList[$i]['product_id']] > $addCount){
                        $totalList[$i]['add'] = $addCount;
                        $countList[$afterProList[$i]['product_id']] -= $addCount;
                    }else{ //剩余商品数 < 该货道可增加最大数 => 全部补入
                        $totalList[$i]['add'] = $countList[$afterProList[$i]['product_id']];
                        $countList[$afterProList[$i]['product_id']] = 0;
                    }
                }else{ // 携带补货商品中存在该货道商品
                    $totalList[$i]['add'] = 0;
                }
            }else{
                $totalList[$i]['product_id'] = $afterSkuSet[$i]['product_id'];
                $totalList[$i]['product_name'] = $afterSkuSet[$i]['product_name'];
                $totalList[$i]['warn'] = 0;
                $totalList[$i]['normal'] = 0;
                $addCount = $afterSkuSet[$i]['sku_size'];

                if(isset($countList[$afterSkuSet[$i]['product_id']])){ //携带补货商品中存在该货道商品
                    if($countList[$afterSkuSet[$i]['product_id']] > $addCount){
                        $totalList[$i]['add'] = $addCount;
                        $countList[$afterSkuSet[$i]['product_id']] -= $addCount;
                    }else{
                        $totalList[$i]['add'] = $countList[$afterSkuSet[$i]['product_id']];
                        $countList[$afterSkuSet[$i]['product_id']] = 0;
                    }
                }else{
                     $totalList[$i]['add'] = 0;
                }
            }

            
        }
        dd($totalList);
        /*  totalList 格式
            [
                1=>[
                    'product_id'=>
                    'product_name'=>
                    'warn'=>$warnCount
                    'normal'=>$normalCount
                ]
                2=>[],
                3=>[]...
            ]
        */
        return $totalList;
    }

    public function getMaxSupplyList($vmId){
        $skuSetList = DB::table('skus')
                            ->where('vm_id',$vmId)
                            ->select('product_id','product_name','sku_size')    
                            ->get();
        $countMaxProList = [];
        foreach ($skuSetList as $sku) {
            if(array_key_exists($sku->product_id,$countMaxProList)){
                $countMaxProList[$sku->product_id]['max'] += $sku->sku_size;
            }else{
                $countMaxProList[$sku->product_id]['product_name'] = $sku->product_name;
                $countMaxProList[$sku->product_id]['max'] = $sku->sku_size;
                
            }
        }
        return $countMaxProList;
    }
    // 用于发送邮件
    public  function getDailyOrdersToSend(Request $request){ 
        $vmId = $request->input('vmId');
        $dailyOrders = Bussiness::getDailyOrders($vmId);
        // dd($dailyOrders);
        $normalOrders = $dailyOrders['normalOrders'];
        $reservedOrders = $dailyOrders['reservedOrders'];
        //统计订单信息
        /*
            $countOrders=>
                        pid => 
                            count => $count,
                            product_name => $product_name,
                            availCountToSale => $availCountToSale
        */
        $countOrders = []; 
        foreach($normalOrders as $normalOrder){
            $n_pid = $normalOrder->product_id;
            if(array_key_exists($n_pid, $countOrders)){
                $countOrders[$n_pid]['count']++;            
            }else{
                $countOrders[$n_pid]['product_name'] = $normalOrder->product_name;
                $countOrders[$n_pid]['count'] = 1;
            }
        } 
        foreach($reservedOrders as $reservedOrder){
            $r_pid = $reservedOrder['product_id'];
            if(array_key_exists($r_pid, $countOrders)){
                $countOrders[$r_pid]['count']++;            
            }else{
                $countOrders[$r_pid]['count'] = 1;
            }
        }

        // 售货机可以存储商品最大量
        $countMaxProList = $this->getMaxSupplyList($vmId);
        // dd($countMaxProList);
        // dd($countOrders);

        $vmProList = $this->getNowProList($vmId);

        //统计已存在售货机商品数量
        $vmAfterProList = [];
        foreach ($vmProList as $pro) {
            if($pro->status == 2){
                if(isset($vmAfterProList[$pro->product_id])){
                    $vmAfterProList[$pro->product_id]++;
                }else{
                    $vmAfterProList[$pro->product_id] = 1;
                }
            }
        }
        // dd($vmProList);
        // dd($vmAfterProList);
        $finalList = [];
        foreach($countMaxProList as $p_id => $pro){
            // 携带最小值
            $finalList[$p_id]['product_name'] = $pro['product_name'];
            if(array_key_exists($p_id, $countOrders) && array_key_exists($p_id, $vmAfterProList)){
                $min = $countOrders[$p_id]['count'] - $vmAfterProList[$p_id];
                $min = ($min>0)?$min:0;

            }else if(array_key_exists($p_id, $countOrders) && !array_key_exists($p_id, $vmAfterProList)){
                $min = $countOrders[$p_id]['count'];
            }else{
                $min = 0;
            }
            
            $finalList[$p_id]['min'] = $min;

            //携带最大值
            if(array_key_exists($p_id, $vmAfterProList)){
                $max = $pro['max'] - $vmAfterProList[$p_id];
            }else{
                $max = $pro['max'];
            }
            $finalList[$p_id]['max'] = $max;
        }
        dd($finalList);
        return json_encode($countOrders); 

    }


    public function add(){
        for ($i=6; $i <=59 ; $i++) { 
            DB::table('skus')->insert([
                    'seq' => $i,
                    'vm_id' => 1001,
                    'vm_name' => '友宝四层',
                    'product_id' => 100000+$i,
                    'product_name' => '光明鲜奶'.$i,
                    'sku_size' => 5,
                    'original_price' => 300,
                    'retail_price' => 270
                ]);
            
        }
    }
    
}
