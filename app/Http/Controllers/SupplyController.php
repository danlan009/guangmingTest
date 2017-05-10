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
class SupplyController extends Controller 
{
    // 补货控制器   
    public function test(){
        // $phone = User::getPhone('www');
        // dd($phone);
        // $mallService = new MallService();
        // $vminfo = $mallService->getVmInfo('0081008');
        // dd($vminfo);
        // $num = $mallService->getNumOfSale('0081008',100010);
        // dd($num);
        // $res = $mallService->singleBuyCode(10,20,100010,'0081008');
        // dd($res);
        // $proList = $mallService->showPros('0081008','book');
 
        // $list = $mallService->getNumOfBook('0081008',100006);
        // dd($list);

        // $url = $mallService->getImg('products',100016,'l');
        // dd($url);
        // $supplyService = new SupplyService();
        // $data = $supplyService->getSupplyData('0081008');
        // dd($data);
        // $orderService = new OrderService();
        // $data = $orderService->handleOrdersToAllot('0081008');
    
        

       
        // $this->finishSupply();
        

        // $this->getDateAfterWeekDays(30);

        return view('supply.startSupplyment');
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
        // foreach ($supplyData as $key => $data) {
        //     $supplyData[$key] = (array)($data);
        // }
        // dd($supplyData);
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
}
