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
    public function showPros($vmId){ //根据vmid 拉取所有商品
        $proLists = Skus::getAllPros($vmId);
        $exps = DB::table('products')->get();
        // 放入缓存
        foreach ($proLists as $k=>$pro) {
            foreach ($exps as $exp) { //拼接商品生存期
                if($pro['product_id'] == $exp->id){
                    $proLists[$k]['exp'] = $exp->exp;
                }
            }
            Cache::put('PRO_DETAIL_'.$proLists[$k]['product_id'].'_'.$vmId,$proLists[$k],1440);
        }
        return json_encode($proLists);
    }

    public function getProDetail(Request $request){
        $pid = $request->input('pid');
        $vmId = $request->input('vmId');
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

    // 即卖买码
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

    //脚本自动批量买码
    public function dailyBuyCodes($vmId){
    	$dailyOrders = Bussiness::getDailyOrders($vmId);
    	// dd($dailyOrders['normalOrders'][0]->vmid);
    	$normalOrders = $dailyOrders['normalOrders'];
    	$reservedOrders = $dailyOrders['reservedOrders'];
    	//写入orders_log
    	$time = date('Y-m-d');
        $expiresTime = '';//取货码有效期 暂定
 
        $normalCount = count($normalOrders);
        
        $blnoArr = $this->createBlno($normalCount);
        Log::info('script_buy_codes---get::'.json_encode($blnoArr));
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
        return 1;
        Log::info('daily_buy_codes---successful,resolved---dailyOrders::'.json_encode($dailyOrders));
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
    public function test(Request $request){
        $this->singleBuyCode(5,11,100010,1001);
    } 

    // 售货机列表
    public function vmList(){
        $vms = DB::table('vms')
                    ->select('id','vm_name')
                    ->get();
        return view('wx.vmList', array(
                'vms' => 'test: vm list'
            ));
    }

    // 商品列表
    public function productsList($vmid){

        return view('wx.proList', array(
                'vmid' => $vmid
            ));
    }

    // 商品详情
    public function productDetail($pid){

        return view('wx.details', array(
                
            ));
    }

    // 预定结果
    public function result(){

        return view('wx.result', array(
                
            ));
    }

    // 我的订单
    public function myorders(){
        // 获取用户信息

        return view('wx.myOrders', array(
                
            ));
    }

    // 我的微信卡券列表
    public function wxCards(){
        $wxId = '';

        return view('wx.wxCards', array(
                
            ));
        
    }

}

