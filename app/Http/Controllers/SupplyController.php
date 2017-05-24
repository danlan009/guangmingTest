<?php
 
namespace App\Http\Controllers;  

use App\Model\SkuSupply; 
use App\Model\SupplyLog;
use App\Model\OrderLog; 
use App\Model\User; 
use App\Model\Order; 
  
use Illuminate\Http\Request; 
use App\Lib\Bussiness;  
  
use App\Service\MallService; 
use App\Service\SupplyService;
use App\Service\OrderService; 
use App\Service\CouponService;

use EasyWeChat\Foundation\Application;
class SupplyController extends Controller 
{ 
    // 补货控制器   
    public function test(Application $app){
        // $phone = User::getPhone('qqqq');
        // dd($phone);
        // $mallService = new MallService();
        // $vminfo = $mallService->getProDetail(100016,'0081008','book');
        // $proList = $mallService->showPros('0081008','book');
        // dd($proList);
        // $json = json_encode($proList);
        // return view('supply.test',['proList'=>$json]);
        // $num = $mallService->getNumOfSale('0081008',100010);
        // dd($num);
        // $res = $mallService->singleBuyCode(10,20,100010,'0081008');
        // dd($res);
  
        // $list = $mallService->getNumOfBook('0081008',100006);
        // dd($list);

        // $url = $mallService->getImg('products',100016,'l');
        // dd($url);
        $supplyService = new SupplyService();
        $data = $supplyService->getDailyOrdersToSend('0081008',1);
        dd($data);
        // $orders = (new OrderLog)->getReservedOrders('0081008');
        // dd($orders);
        // $orderService = new OrderService();
        // $data = $orderService->getDailyOrdersToSend('0081008');
        // dd($data);
    
        // $data = json_decode($str,true);
        // dd($data);

       
        // $this->finishSupply();
        

        // $this->getDateAfterWeekDays(30);

        // return view('supply.startSupplyment');
        // $now = date('Y-m-d');
        // $orderId = 2;
        // $stopLog = \App\Model\OrderStop::where('order_id',$orderId)
        //                                 ->where('end_date','>=',$now)
        //                                 ->orWhereNull('end_date')
        //                                 ->where('start_date','<=',$now)
        //                                 ->get()
        //                                 ->toArray(); 
        // dd($stopLog);
        // $couponService = new CouponService();
        // session(['wxId'=>'oSIrewgw5OTIz-FR00J1pzmCbhYU']); //测试
        // $access_token = $app->access_token;
        // $access_token = $access_token->getToken();

        // \Log::debug('test --- get access_token:'.json_encode($access_token));
        // dd($access_token);
        // $list = $couponService::getCardList($access_token);
        // \Log::debug('cardList---returns:'.json_encode($list));
        // dd($list);
        // $card_id = "pSIrewg7XQUyrb1fE-yLUUv_nO38";
        // $cardDetail = $couponService::getCardDetail($app,$card_id);
        // dd($cardDetail);
        // $arr = [1,2,3,4];
        // $arr = [];
        // foreach($arr as $item){
        //     echo $item;
        // }
    }

    public function myTest(Application $app){
        //dd("dd");
        $supply = new SupplyService();
        $supply->sendSuccessNotifyToUser($app);

    }

    public function startSupplyment(){
        $mode = 1; // 正常补货
        $mallService = new MallService();
        $nodes = $mallService->getNodeList();
        // dd($nodes);
        return view('supply.startSupplyment',array(
                'mode' => $mode,
                'nodes' => $nodes
            ));
    }

    public function listSkus(Request $request){
        $vmid = $request->input('vmid');
        // var_dump($vmid);die;
        $request->session()->put('vmid',"$vmid");
        $supplyService = new SupplyService();
        $supplyData = $supplyService->getSupplyData($vmid);
        // dd(json_encode($supplyData));
        if(empty($supplyData)){
            return '该售货机尚未配置货道!';
        }
        return view('supply.listSkus',array(
                'supplyData' => json_encode($supplyData),
                'vmid' => $vmid
            ));
    }

    public function ajaxReceiveData(Request $request){
        $data = $request->input('data');
        $vmid = $request->input('vmid');
        $array_data = json_decode($data,true);
        SupplyService::handleFinishData($array_data,$vmid);
        return 1;
        
    } 

    public function ajaxClear(Request $request){
        $vmid = $request->input('vmid');
        if($vmid){
            // sku_supplys
            $supplyService = new SupplyService();
            $supplyService->clearVm($vmid);
            // supply_logs
            \DB::table('supply_logs')->insert([
                    'vmid' => $vmid,
                    'operator_wx_id' => 'test',
                    'operator_name' => 'test',
                    'supply_date' => date('Y-m-d H:i:s'),
                    'operation' => 2
                ]);
            return 1;
        }
    }
}