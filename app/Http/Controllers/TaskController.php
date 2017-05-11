<?php

namespace App\Http\Controllers; 
 
use Illuminate\Http\Request;
 
use App\Http\Requests; 
use App\Http\Controllers\Controller; 
use Cache;
use Log;

use App\Model\Order;
use App\Model\OrderStop;

use App\Service\SupplyService;
use App\Service\OrderService;
class TaskController extends Controller
{

    //脚本自动批量买码
    public function dailyBuyCodes($vmId){
    	$dailyOrders = OrderService::getDailyOrders($vmId);
    	// dd($dailyOrders['normalOrders'][0]->vmid);
    	$normalOrders = $dailyOrders['normalOrders'];
    	$reservedOrders = $dailyOrders['reservedOrders'];
    	//写入orders_log
    	$time = date('Y-m-d');
        $expiresTime = '';//取货码有效期 暂定
 
        $normalCount = count($normalOrders);
        
        $blnoArr = $this->createBlno($normalCount);
        Log::debug('script_buy_codes---get::'.json_encode($blnoArr));
        foreach ($normalOrders as $k=>$normalOrder) { //正常订单创建orders_log
            //$price 从缓存中读取,$price 从session中读取
            // dd($normalOrder);
            $product_id = $normalOrder->product_id;
            $order_id = $normalOrder->order_id;
            // dd($order_id);
            $order_detail_id = $normalOrder->order_detail_id;
            if(count($blnoArr)>0){
    	        $orderLogsModel = new OrderLogs;
    		    $orderLogsModel->order_id = $order_id;
    		    $orderLogsModel->order_detail_id = $order_detail_id;
    		    $orderLogsModel->product_id = $product_id;
    		    $orderLogsModel->create_date = $time;
    		    $orderLogsModel->order_status = 201;
    		    $orderLogsModel->is_reserved = 0;
    		    $orderLogsModel->blno = $blnoArr[$k];
    		    $orderLogsModel->vmid = $vmId;
    		    $orderLogsModel->save();
    		}else{ //买码失败
                return 'buyCodeFail!';
            } 
    	}

        $reservedCount = count($reservedOrders);
        $blnoResArr = $this->createBlno($reservedCount);
        foreach ($reservedOrders as $k => $reservedOrder) { //占道订单,重新生成取货码,修改状态
            $product_id = $reservedOrder->product_id;
            $order_id = $reservedOrder->order_id;
            $order_detail_id = $reservedOrder->order_detail_id;

            if(count($blnoResArr)>0){
                $res = OrderLogs::where('order_detail_id',$order_detail_id)->update([
                                                            'create_date'=>$time,
                                                            'order_status'=>201,
                                                            'is_reserved'=>2,
                                                            'blno'=>$blnoResArr[$k]
                                                        ]);
                if(!$res){
                    return 'error';
                }
            }else{ //买码失败
                return 'buyCodeFail!';
            }
        }
        Log::debug('daily_buy_codes---successful,resolved---dailyOrders::'.json_encode($dailyOrders));
        return 1;
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
        $waitOrders = Order::where('order_status',1)
                                ->where('pay_status',1)
                                ->select('id','order_status','start_date','type','rate')
                                ->get();
                                // ->toArray();

        // 2.暂停配送订单
        $stopOrders = Order::where('order_status',4)
                                ->where('pay_status',1)
                                ->select('id','order_status','start_date','type','rate')
                                ->get();

        // 3.配送中订单
        $sendOrders = Order::where('order_status',2)
                                ->where('pay_status',1)
                                ->select('id','order_status','start_date','type','rate')
                                ->get();

        // 处理配送未开始订单
        /*
            今天是配送开始日期
                    |-- 订单为工作日配送
                            |-- 当天是周六/周日 => 改为暂停配送
                            |-- 当天是工作日 
                                    |-- 申请停送 => 改为暂停配送
                                    |-- 未申请停送 => 改为配送中
                    |-- 订单为每天配送
                            |-- 申请停送 => 改为暂停配送
                            |-- 未申请停送 => 改为配送中
        */
        foreach ($waitOrders as $waitOrder) {
            //检查今天是否是配送开始日期
            $startDate = $waitOrder->start_date;
            $rate = $waitOrder->rate;
            $orderId = $waitOrder->id;
            if($now == $startDate){

                $stopLog = OrderStop::where('order_id',$orderId)
                                        ->where('start_date','<=',$now)
                                        ->where('end_date','>=',$now)
                                        ->get()
                                        ->toArray();

                if($rate){ //工作日配送
                    if($day == 6 || $day == 0){ //周六,周日 改为暂停
                        $waitOrder->order_status = 4;
                        $waitOrder->save();
                    }else{ //周一到周五
                       
                        if(!empty($stopLog)){ //停送中
                            $waitOrder->order_status = 4;
                            $waitOrder->save();
                        }else{
                            $waitOrder->order_status = 2;
                            $waitOrder->save();                                
                        }
                    }
                }else{ //每天配送
                    
                    if(!empty($stopLog)){ //停送中
                        $waitOrder->order_status = 4;
                        $waitOrder->save();
                    }else{
                        $waitOrder->order_status = 2;
                        $waitOrder->save();
                    }
                }
            }
        }

        // die;

    	// 2.处理暂停订单
        /*
            订单为工作日配送
                    |-- 当天为工作日 
                            |-- 没有申请停送 => 改为配送中
            订单为每天配送
                    |-- 没有停送申请 => 改为配送中
        */
		foreach ($stopOrders as $stopOrder) {
			$length = $stopOrder->type;
			$start = $stopOrder->start_date;
			$rate = $stopOrder->rate;
            $orderId = $stopOrder->id;

            //判断是否停送
            $stopLog = OrderStop::where('order_id',$orderId)
                                    ->where('start_date','<=',$now)
                                    ->where('end_date','>=',$now)
                                    ->get()
                                    ->toArray();
            //判断订单 配送频率(工作日/每天)
			if($rate){ // 工作日配送
                                
                if($day <= 5 && $day > 0){ //周一到周五,停送到最后一天
                    if(empty($stopLog)){
                        $stopOrder->order_status = 2;   
                        $stopOrder->save();  
                    }
                    
                }
                
                
            }else{ //每天配送
        
                if(empty($stopLog)){ //没有停送申请
                    $stopOrder->order_status = 2;
                    $stopOrder->save();
                }
            }
		}

        // die;
        
        // 3.处理配送中订单,检查是否暂停配送/配送完成
        /*
            订单为工作日配送
                    |-- 今天日期大于(开始时间+天数+暂停天数) => 配送完成
                    |-- 今天日期小于等于配送最后一天日期
                                |-- 今天是周六/周日 => 暂停配送
                                |-- 今天是工作日
                                        |-- 有停送申请 => 暂停配送
            订单为每天配送
                    |-- 今天日期大于(开始时间+天数+暂停天数) => 配送完成
                    |-- 有停送申请 => 暂停配送

        */
        foreach ($sendOrders as $sendOrder) {
            $length = $sendOrder->type;
            $start = $sendOrder->start_date;
            $rate = $sendOrder->rate;
            $orderId = $sendOrder->id;
            \Log::debug('sendOrders handling sendOrder returns'.json_encode($sendOrder));
            \Log::debug('sendOrders handling orderid returns'.$orderId);
            // 计算最后一天配送日期
            // $lastSendDay = date('Y-m-d',strtotime("+".$length." day",strtotime($start))-1);
            $lastSendDay = $this->getDateAfterWeekDays($length);

            $stopLog = OrderStop::where('order_id',$orderId)
                                        ->where('start_date','<=',$now)
                                        ->where('end_date','>=',$now)
                                        ->get()
                                        ->toArray(); 
            // dd($stopLog);
            // \Log::debug('sendOrders handling stopLog returns ---'.json_encode($stopLog));
            // dd($lastSendDay);
            // 工作日配送
            if($rate){
                if($lastSendDay < $now){ //配送完成
                    $sendOrder->order_status = 3;
                    $sendOrder->save();
                }else{
                    if($day == 6 || $day == 0){ // 周六周日
                        $sendOrder->order_status = 4; //暂停
                        $sendOrder->save();
                    }else{ //周一到周五
                        
                        if(!empty($stopLog)){ // 有停送申请 订单状态改为暂停
                            $sendOrder->order_status = 4;
                            $sendOrder->save();
                        }
                        
                    }
                }
                
            //每天配送
            }else{
                if($lastSendDay < $now){ //是最后一天配送
                    $sendOrder->order_status = 3; //配送完成
                    $sendOrder->save();
                }else{
                   
                    
                    if(!empty($stopLog)){ //有停送申请 订单状态改为暂停
                        $sendOrder->order_status = 4;
                        $sendOrder->save();
                    }
                }
            }
        }
    }


    // 清理缓存
    public function flushCache(){
        Cache::flush();
        return 1;
    }

    // md5_file() 检测图片是否修改,修改则更新版本号并放置缓存
    public function updateImg(){
        $root = public_path().'/file_img/images'; //需要修改,上线时更改到指定服务器图片目录
        $this->my_scandir($root);
    }

    public function my_scandir($dir)
    {
        if(is_dir($dir))
        {
            if($handle=opendir($dir))
            {
                while(($file=readdir($handle))!==false)
                {
                    if($file!="." && $file!="..")
                    {
                        if(is_dir($dir."/".$file))
                        {
                            $this->my_scandir($dir."/".$file);
                        }
                        else
                        {
                            $md5 = md5_file($dir.'/'.$file);
                            $fileTag = "{$dir}/$file";
                            $v = substr($md5,22);
                            Cache::put("API_IMG_MD5_$fileTag", $v ,1440); 
                            Log::debug('TaskController--- updateImg --- make a new fileTag::'."API_IMG_MD5_$fileTag?v=".$v);
                        }
                    }
                }
                closedir($handle);
            }
        }
    }

    // 获取经过指定工作日(周一到周五)后的日期
    public function getDateAfterWeekDays($count){

        $now = time();
        $timer = strtotime(date('Y-m-d',$now));
        for ($i=1; $i <= $count; $i++) { 
            $timer = $timer+3600*24;
            $num = date('N',$timer);
            if($num == 6 || $num == 7){
                $i--;
            }
        }
        $date = date('Y-m-d',$timer);
        return $date;
    }
}
