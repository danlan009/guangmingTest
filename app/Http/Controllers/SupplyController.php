<?php
 
namespace App\Http\Controllers; 

use App\Model\SkuSupply; 
use App\Model\OrderLog; 
use App\Model\User; 
 
use Illuminate\Http\Request; 
use App\Lib\Bussiness;  
 
use App\Service\MallService; 
use App\Service\SupplyService;
use App\Service\OrderService;
 
use EasyWeChat\Foundation\Application;
use Log;
class SupplyController extends Controller 
{ 
    // 补货控制器   
    public function test(){
        // $userService = $wechat->user;

        $couponService = new CouponService();
        session(['wxId'=>'SIrewrv8f8UgNWp8u_qYwhwCM6s']);
        $cardList = $couponService->getCardList($app);
        dd($cardList);
        $card = $wechat->card;
        $res = $card->getUserCards('oidFcxGkJZygk-wpjP64WakpxwkE');
        dd($res);
        // $user = $userService->get('oidFcxGkJZygk-wpjP64WakpxwkE');
        // echo $user->nickname;
        
        // return phpinfo();
        // echo 111;

    } 


    public function serve(Application $wechat)
    {
        Log::info('request arrived.');
        $server = $wechat->server;
        $server->setMessageHandler(function($message){
            Log::debug('Message:'.json_encode($message));
            if(strtolower($message['MsgType']) == 'event') {
                switch (strtolower($message->Event)) {
                    case 'location':
                        $this->getLocation($message);
                        break;
                    case 'click':
                        return $this->sendMsg($message);
                        break;
                    default:
                        //return "Hello";
                        break;
                }
            }
        });

        Log::info('return response.');
        $response  = $server->serve();
        return $response->send();
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
        $supplyService = new SupplyService();
        $supplyData = $supplyService->getSupplyData($vmid);
        
        return view('supply.listSkus',array(
                'supplyData' => json_encode($supplyData)
            ));
    }

    
    public function finishSupply(){
        // $data = $request->input('data');
        $supplyService = new SupplyService();
        
        
        $supply_sku_info = $supplyService->getSupplyData('0081008');
        $date = date('Y-m-d');
        foreach ($supply_sku_info as $k => $sku) {
            // 修改测试数据 
            if($sku['default_add'] == 0){
                $supply_sku_info[$k]['actual_add'] = 0;
            }else{
                $supply_sku_info[$k]['actual_add'] = $sku['default_add'];
                
            }
        }

        $supplyService->handleFinishData($supply_sku_info,'0081008');
        
    }

    public function ajaxReceiveData(Request $request){
        // $data = $_POST['data'];
        $data = $request->input('data');
        // dd($data);
        echo "'".$data."'";
        die;
        $array_data = json_decode($data,true);
        dd($array_data);
        // 写入数据库
        foreach ($array_data as $seq => $sku) {
            SkuSupply::create([

                ]);
        }
    }
}
