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
use Cache;

class SupplyController extends Controller 
{ 
    // 补货控制器   
    public function test(){
        Cache::put('username','dongfan',60);
        
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
