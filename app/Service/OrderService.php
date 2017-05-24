<?php
namespace App\Service;
use App\Model\Order;
use EasyWeChat\Foundation\Application;
use App\Lib\Http;
use App\Model\OrderLog;
use DB; 
class OrderService{
	//获取某台售货机当天需要买码的订单,
    	//包括正常预定订单,和之前未取且交占道费的订单
    public static function getDailyOrders($vmId){
        $date = date('Y-m-d');
    	//正常日配送订单  
    	$dailyOrders = array();   
  
    	$normalOrders = (new Order)->getNormalOrders($vmId);
    	// dd($normalOrders);
    	$dailyOrders['normalOrders'] = $normalOrders;
    	//获取占道订单
    	$reservedOrders = (new OrderLog)->getReservedOrders($vmId);
    	$dailyOrders['reservedOrders'] = $reservedOrders;
        // dd($dailyOrders);
    	return $dailyOrders;
    	
    }

    // 二次补货获取漏补订单
    public function getLeftOrders(){

    }
    // 正常每日预定/占道订单/即卖订单
    public function getPickupRes($orderId){ 
        // $status = OrderLogs::where('order_id',$orderId)
        //                         ->get();
        // return $status;
    }

    // 获取买码失败订单
    public function getCodeFailOrds($vmId){
        $list = DB::table('order_logs')
                            ->whereNull('blno')
                            ->where('vmid',$vmId)
                            ->get();
        return $list;
    }

    public function getDeliverFailOrd($vmId){ 
        $list = DB::table('order_logs')
                            ->where('order_status',400)
                            ->where('vmid',$vmId)
                            ->get();
        return $list;
    }

    // 补货时,按product_id 分组统计 订单,方便随机分配订单
    public static function handleOrdersToAllot($vmId){
        $dailyOrders = OrderService::getDailyOrders($vmId)['normalOrders'];
        $formatOrders = [];
        foreach ($dailyOrders as $k => $order) {

            $formatOrders[$order->product_id][] = [
                                                    'order_id' => $order->order_id,
                                                    'order_detail_id' => $order->order_detail_id,
                                                  ];
        }
        return $formatOrders;
    }

    //获取昨天未取货订单列表,发是否占道通知
    public function sendNotifyTodayFailOrderList(Application $app){
        $order_log_list = OrderLog::queryTodayFailOrderList();
        $notice = $app->notice;
        $userService = $app->user;

        $templateId = '';
        $arr = array();
        $i=0;
        if(count($order_log_list)>0){
            $res = array();
            //整理拼接的数据（按照wx_id）
            foreach ($order_log_list as $k){
                $res[$k->wx_id][$k->phone][] = $k;
            }

            foreach ($res as $k=>$v){
                $openid = $k;
                $user = $userService->get($k);
                foreach ($v as $key=>$value){
                    foreach ($value as $value){
                        if($user['subscribe']==0){
                            $smsparams = array('phone' =>$key,

                             'msg' =>"您今天的订单没有收取",

                             'night' =>1,

                            );
                            $arr[$i] = Http::curlPost("http://sms2.uboxol.com/send_sms",$smsparams);


                       }else{
                            $url = '';
                            $data = '';
                            $arr[$i] = json_decode($notice->uses($templateId)->withUrl($url)->andData($data)->andReceiver($openid)->send());

                       }
                       $i++;

                    }
                }
                //dd($v);

            }

        }
        return $arr;
    }

    //获取配送时间
    public static function getDispatchingDate($orderId){
        //首先要获取订单的信息
        $order_list = DB::table('orders')->where('id',$orderId)->first();
        $arr = array();
        if ($order_list){
            $now =$order_list->start_date;
            $date = '';
            $rest_date = '';

            //在order_stop的查找天数
            $stop_day = DB::table('order_stop')->where('order_id',$order_list->id)->whereNotNull('stop_day')->where('stop_day','>',0)->sum('stop_day');
            $total = '';

            if($stop_day){
                $total = $stop_day + $order_list->type;

            }else{
                $total = $order_list->type;

            }

            if ($order_list->rate==0){

                $date = date("Y-m-d",strtotime("+$total days",strtotime($now)));
                $rest_date = ceil( (strtotime($date) - strtotime(date('Y-m-d'))) / 86400);

            }else{
                //排除
                $timer = strtotime($now);
                for ($i=1; $i <=$total; $i++) {
                    $timer = $timer+3600*24;
                    $num = date('N',$timer);
                    if($num == 6 || $num == 7){
                        $i--;
                    }
                }
                $date = date('Y-m-d',$timer);
                $k=0;
                $day = ceil( (strtotime($date) - strtotime('now')) / 86400);

                for($i=0;$i<$day;$i++){
                    $a = date('w',strtotime("-$i days"));
                    if($a=="0"||$a=="6"){

                    }else{
                        $k++;
                    }
                }

                $rest_date = $k;

            }
            $arr = [
                'start_date'=>$order_list->start_date,
                'end_date'=>$date,
                'rest_date'=>intval($rest_date)
            ];

        }
        //dd($arr);
        return $arr;

    }
}
?>