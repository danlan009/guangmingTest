<?php
namespace App\Service;
use App\Service\OrderService;

class SupplyService{ 

    // 获取所有补货员列表
    public function getSenderList(){
        $senderList = file_get_contents(env('SENDER_FILE_PATH'));
        if($senderList){
            Log::debug('get senderlist from json---'.json_encode($senderList));
            return json_decode($senderList,true);
        }
    }

	// 补货前获取售货机内现有商品列表
    public function getNowProList($vmId){
        // $vmId = $request->input("vmId");
        
        $proList = \DB::table('skus')
                            ->join('sku_supplys','skus.id','=','sku_supplys.sku_id')
                            ->select('skus.id','skus.seq','skus.product_id','skus.product_name','skus.sku_size','sku_supplys.location','sku_supplys.status')
                            ->where('vm_id',$vmId)
                            ->where('status','<>',1) //未出货
                            ->get();
        \Log::debug('SupplyService---getNowProList returns:'.json_encode($proList));
        return $proList;
    }

    // 补货时计算补货数据
    public function getSupplyData($vmid){ 
        if(!isset($vmid)){
            return 'parameter missing!';
        }
        $vmSkuNum = \DB::table('vms')
                            ->where('vmid',$vmid)
                            ->value('sku_num');
                            // ->get();
        // 获取货道配置商品信息
        $skuSet = \DB::table('skus')
                            ->where('vm_id',$vmid)
                            ->select('seq','product_name','product_id','sku_size')
                            ->get();
        // dd($skuSet);
        // 处理数据
        $afterSkuSet = [];
        foreach ($skuSet as $list) {
            $afterSkuSet[$list->seq]['product_id'] = $list->product_id;
            $afterSkuSet[$list->seq]['product_name'] = $list->product_name;
            $afterSkuSet[$list->seq]['sku_size'] = $list->sku_size;
        }

        ksort($afterSkuSet);
        // dd($afterSkuSet);              
        // dd($vmSkuNum);
        $proList = $this->getNowProList($vmid); //返回售货机内已存在商品列表
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

        $dailyOrders = OrderService::getDailyOrders($vmid);
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
        $realList = [];
        foreach($afterSkuSet as $k=>$sku) { 
            $pid = $sku['product_id'];
            \Log::debug('test---condition foreach afterSkuSet come in!');
            $realList[$k]['product_id'] = $pid;
            $realList[$k]['product_name'] = $sku['product_name'];
            $realList[$k]['sku_size'] = $sku['sku_size'];
            if(in_array($pid, $countList)){ //该货道配置的商品需要补货
                Log::debug('test---condition in_array come in!');
                if(isset($afterProList[$k])){ //该货道已存有商品

                    $realList[$k]['normal'] = $afterProList[$k]['normal']; //存入正常商品数量
                    $realList[$k]['warn'] = $afterProList[$k]['warn']; //存入过期预警商品数量

                    $available = $sku['sku_size'] - $afterProList[$k]['normal']; //计算该货道最多可补件数
                    
                    if($countList[$pid] >= $available){ //该货道不能容纳全部商品,需下一个货道
                        $countList[$pid] -= $available;
                        $realList[$k]['add'] = $available;
                    }else{
                        $realList[$k]['pid'] = $countList[$pid];
                        $countList[$pid] = 0;
                    }
                }else{ //该货道为空
                    
                    $realList[$k]['normal'] = 0;
                    $realList[$k]['warn'] = 0;

                    $available = $sku['sku_size'];
                    if($countList[$pid] >= $available){
                        $realList[$k]['add'] = $sku['sku_size'];
                        $countList[$pid] -= $available;
                    }else{
                        $realList[$k]['add'] = $countList[$pid];
                        $countList[$pid] = 0;
                    }
                    
                }
                
            }
        }
        // echo 1;
        dd($realList);
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

    public function getMaxSupplyList($vmid){
        $skuSetList = DB::table('skus')
                            ->where('vmid',$vmid)
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
        $vmid = $request->input('vmid');
        $dailyOrders = Bussiness::getDailyOrders($vmid);
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
        $countMaxProList = $this->getMaxSupplyList($vmid);
        // dd($countMaxProList);
        // dd($countOrders);

        $vmProList = $this->getNowProList($vmid);

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
}
?>