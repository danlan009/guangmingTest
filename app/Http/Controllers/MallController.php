<?php

namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
 
use App\Model\Orders;
use App\Model\OrderLogs;
use App\Model\OrderStops; 
use App\Model\Skus; 
use App\Lib\Bussiness;
use DB;
use Cache; 
use Log;
class MallController extends Controller
{
    public function listAllPros(Request $request){ //根据vmid 拉取所有商品
        $vmId = $request->input('vmId');
        // dd($vmId);
        $proLists = Skus::getAllPros($vmId);
        // 放入缓存
        foreach ($proLists as $pro) {
            Cache::put('PRO_DETAIL_'.$pro['product_id'].'_'.$vmId,$pro,1440);
        }
        return json_encode($proLists);
        // if(!empty($proLists['products'])){
        //     $products = $list['products'];
        //     foreach($products as $k=>$v){
        //         Cache::put('PRO_DETAIL_'.$v['id'].'_'.$vmid,$v,1440);
        //     }
        //     return $products;
        // }
        // dd($proList);
    }

    public function getProDetail(Request $request){
        $pid = $request->input('pid');
        $vmId = $request->input('vmId');
        if(empty($pid) || empty($vmId)){
            return 'error';
        }
        $proDetail = Cache::get('PRODUCT_DETAIL_'.$pid.'_'.$vmid);
        if(empty($proDetail)){ //需要重新拉取售货机商品列表,放入缓存
           $list = $this->listAllPros($vmId);
           $proDetail = $list[$pid];
        }
        if(!empty($proDetail)){
            return $proDetail;
        }else{
            return 'error';
        }
    }

    
    public function dailyBuyCodes($vmId){
    	// dd($ordersLogModel);
    	$dailyOrders = Bussiness::getDailyOrders($vmId);
    	// dd($dailyOrders['normalOrders'][0]->vmid);
    	$normalOrders = $dailyOrders['normalOrders'];
    	$reservedOrders = $dailyOrders['reservedOrders'];
    	//写入orders_log
    	$time = date('Y-m-d');
    	$orderLogsModel = new OrderLogs();
    	$expiresTime = '';//取货码有效期 暂定

        foreach ($normalOrders as $normalOrder) { //正常订单创建orders_log
            //$price 从缓存中读取,$price 从session中读取

            $product_id = $normalOrder->product_id;
            $order_id = $normalOrder->orderId;
            $order_detail_id = $normalOrder->order_detail_id;
    		$price = '';
    		$userId = '';
    		$res = $api->orderCode($vmid, $product_id, $price,$userId, $orderId,$expiresTime);
    		if(!empty($r['head']['return_code']) && $r['head']['return_code'] == 200){
    		    $blno = $r['body']['delivery_code'];
    		    $tran_id = $r['body']['tran_id'];
    		    $ordersLogModel->order_id = $order_id;
    		    $ordersLogModel->order_detail_id = $order_detail_id;
    		    $ordersLogModel->product_id = $product_id;
    		    $ordersLogModel->create_date = $time;
    		    $ordersLogModel->order_status = 201;
    		    $ordersLogModel->isReserved = 0;
    		    $ordersLogModel->blno = $blno;
    		    $ordersLogModel->tran_id = $tran_id;
    		    $ordersLogModel->vmid = $vmId;
    		    $ordersLogModel->save();
    		}else{ //买码失败
                
            } 
    	}

        foreach ($reservedOrders as $key => $reservedOrder) { //占道订单,重新生成取货码,修改状态
            $product_id = $reservedOrder->product_id;
            $order_id = $reservedOrder->orderId;
            $order_detail_id = $reservedOrder->order_detail_id;
            $price = '';
            $userId = '';

            $res = $api->orderCode($vmid, $product_id, $price,$userId, $orderId,$expiresTime);
            if(!empty($r['head']['return_code']) && $r['head']['return_code'] == 200){
                $blno = $r['body']['delivery_code'];
                $tran_id = $r['body']['tran_id'];
                
                $ordersLogModel->create_date = $time;
                $ordersLogModel->order_status = 201;
                $ordersLogModel->isReserved = 2;
                $ordersLogModel->blno = $blno;
               
                $ordersLogModel->save();
            }else{ //买码失败

            }
        }
    } 
    
    public function dailyCheckOrders(){ // 每日定时任务修改订单状态 是否改为配送完成/暂停配送/配送中
    	//根据orders.rate 和 order_stop 判断是否停送,修改总订单状态
    	//判断是否配送完成
    	$now = date('Y-m-d');
        //判断当天是否是周末
        $day = date('w');
        // dd($day);
        $tomorrow = date('Y-m-d',strtotime("+1 day"));
        
        // 1.处理待配送订单
        $waitOrders = Orders::where('orders_status',1)
                                ->where('pay_status',1)
                                ->select('id','orders_status','start_date','type','rate')
                                ->get();

        $length = $waitOrders->type;
        $start = $waitOrders->start_date;
        $rate = $waitOrders->rate;
        $orderId = $waitOrders->order_id;

        foreach ($waitOrders as $waitOrder) {
            //检查明天是否是配送开始日期
            $startDate = $waitOrder->start_date;
            if($tomorrow == $now){
                if($rate){ //工作日配送
                    if($day == 5 || $day ==6){ //周五,周六 改为暂停
                        $waitOrder->orders_status = 4;
                        $waitOrder->save();
                    }else{ //周一,周二,周三,周四,周日
                        $stopLog = OrderStop::where('order_id',$orderId)
                                                ->where('start_date','<=',$now)
                                                ->where('end_date','>=',$now)
                                                ->get();
                        if($stopLog){ //有停送申请
                            $waitOrder->order_status = 4;
                            $waitOrder->save();
                        }else{
                            $waitOrder->order_status = 2;
                            $waitOrder->save();                                
                        }
                    }
                }else{ //每天配送
                    $stopLog = OrderStop::where('order_id',$orderId)
                                            ->where('start_date','<=',$now)
                                            ->where('end_date','>=',$now)
                                            ->get();
                    if($stopLog){ //有停送申请
                        $waitOrder->order_status = 4;
                        $waitOrder->save();
                    }else{
                        $waitOrder->order_status = 2;
                        $waitOrder->save();
                    }
                }
            }
        }
    	// 2.处理暂停订单
		//获取所有暂停订单
		$stopOrders = Orders::where('orders_status',4)
                                ->where('pay_status',1)
								->select('id','orders_status','start_date','type','rate')
								->get();
								// ->toArray();
		foreach ($stopOrders as $stopOrder) {
			$length = $stopOrder->type;
			$start = $stopOrder->start_date;
			$rate = $stopOrder->rate;
            $orderId = $stopOrder->order_id;
            //判断订单 配送频率(工作日/每天)
			if($rate){ // 工作日配送
                
                //判断是否已申请停送
                $stopLog = OrderStop::where('order_id',$orderId)
                                        ->where('start_date','<=',$now)
                                        ->where('end_date','>=',$now)
                                        ->get();
                if($day == 0){
                    if(!$stopLog){ //没有申请停送记录,则将暂停中改为配送中
                        $stopOrder->orders_status = 2;
                        $stopOrder->save();
                    }else{
                        $isLastStop = ($stopLog->end_date == $now);
                        if(isLastStop){ //有停送申请,但是最后一天
                            $stopOrder->orders_status = 2;
                            $stopOrder->save();
                        }
                    }
                }else if($day <= 5 && $day > 0){ //工作日,停送到最后一天
                    $isLastStop = ($stopLog->end_date == $now);
                    if($isLastStop){
                        $stopOrder->orders_status = 2;   
                        $stopOrder->save();  
                    }
                }
                
            }else{ //每天配送
                $stopLog = OrderStop::where('order_id',$orderId)
                                        ->where('start_date','<=',$now)
                                        ->where('end_date','>=',$now)
                                        ->get();
                if($stopLog){ //有停送记录并且是最后一天停送,则修改状态
                    $isLastStop = ($stopLog->end_date == $now);
                    if($isLastStop){
                        $stopOrder->orders_status = 2;
                        $stopOrder->save();
                    }
                }
            }
		}
        
        // 3.处理配送中订单,检查是否暂停配送
        $sendOrders = Orders::where('orders_status',2)
                                ->where('pay_status',1)
                                ->select('id','orders_status','start_date','type','rate')
                                ->get();
                                // ->toArray();
        foreach ($sendOrders as $sendOrder) {
            $length = $sendOrder->type;
            $start = $sendOrder->start_date;
            $rate = $sendOrder->rate;
            $orderId = $sendOrder->order_id;

            // 计算最后一天配送日期
            $lastSendDay = date('Y-m-d',strtotime("$start+$length day"));
            //工作日配送
            if($rate){
                if($day == 5){
                    if($lastSendDay == $now){ //周五恰好为配送最后一天
                        $sendOrder->orders_status = 3; //配送完成
                        $sendOrder->save();
                    }else{ //不是最后一天 则改为暂停
                        $sendOrder->orders_status = 4; 
                        $sendOrder->save();
                    }
                }else if($day>=1 && $day<=4){ //周一到周四
                    if($lastSendDay == $now){
                        $sendOrder->orders_status = 3; 
                    }else{
                        $stopLog = OrderStop::where('order_id',$orderId)
                                        ->where('start_date','<=',$now)
                                        ->where('end_date','>=',$now)
                                        ->get();
                        if($stopLog){ // 有停送申请 订单状态改为暂停
                            $sendOrder->orders_status = 4;
                            $sendOrder->save();
                        }
                    }
                }
            //每天配送
            }else{
                if($lastSendDay == $now){ //是最后一天配送
                    $sendOrder->order_status = 3; //配送完成
                    $sendOrder->save();
                }else{
                    $stopLog = OrderStop::where('order_id',$orderId)
                                        ->where('start_date','<=',$now)
                                        ->where('end_date','>=',$now)
                                        ->get();
                    if($stopLog){ //有停送申请 订单状态改为暂停
                        $sendOrder->order_status = 4;
                        $sendOrder->save();
                    }
                }
            }
        }
    }

    public function test(Request $request){
        $count = $request->input('count');
        $time = time();
        for ($i=0; $i < count; $i++) { 
            $blno = Bussiness::createBlno();
            if(in_array($blno, haystack)){}
        }
    
    } 
}
