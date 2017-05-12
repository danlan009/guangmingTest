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
        // $supplyService = new SupplyService();
        // $data = $supplyService->getSupplyData('0081008');
        // dd($data);
        // $orderService = new OrderService();
        // $data = $orderService->handleOrdersToAllot('0081008');
    
        // $str = '{"1":{"product_id":100002,"product_name":"纯牛奶","sku_id":100062,"sku_size":10,"normal":3,"warn":0,"default_add":0,"actual_add":0},"2":{"product_id":100003,"product_name":"鲜牛奶","sku_id":100063,"sku_size":10,"normal":7,"warn":0,"default_add":3,"actual_add":3},"3":{"product_id":100002,"product_name":"光明酸奶","sku_id":100003,"sku_size":5,"normal":0,"warn":0,"default_add":0,"actual_add":0},"4":{"product_id":100002,"product_name":"光明酸奶","sku_id":100004,"sku_size":5,"normal":0,"warn":0,"default_add":0,"actual_add":0},"5":{"product_id":100002,"product_name":"光明酸奶","sku_id":100005,"sku_size":5,"normal":0,"warn":0,"default_add":0,"actual_add":0},"6":{"product_id":100006,"product_name":"光明鲜奶6","sku_id":100007,"sku_size":5,"normal":1,"warn":0,"default_add":2,"actual_add":2},"7":{"product_id":100007,"product_name":"光明鲜奶7","sku_id":100008,"sku_size":5,"normal":1,"warn":0,"default_add":1,"actual_add":1},"8":{"product_id":100008,"product_name":"光明鲜奶8","sku_id":100009,"sku_size":5,"normal":1,"warn":0,"default_add":1,"actual_add":1},"9":{"product_id":100009,"product_name":"光明鲜奶9","sku_id":100010,"sku_size":5,"normal":2,"warn":0,"default_add":2,"actual_add":2},"10":{"product_id":100010,"product_name":"光明鲜奶10","sku_id":100011,"sku_size":5,"normal":4,"warn":0,"default_add":1,"actual_add":1},"19":{"product_id":100001,"product_name":"光明鲜奶","sku_id":100064,"sku_size":5,"normal":5,"warn":0,"default_add":0,"actual_add":0}}';
        // $data = json_decode($str,true);
        // dd($data);

       
        // $this->finishSupply();
        

        // $this->getDateAfterWeekDays(30);

<<<<<<< HEAD
        // return view('supply.startSupplyment');
        // return phpinfo();
        echo 111;
=======
        return view('supply.startSupplyment');
        // return phpinfo();
        // echo 111;
>>>>>>> 3a4b754ac165091d4e37cfda04f057fe732add00
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
