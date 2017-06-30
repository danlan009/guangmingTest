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

use Mail;
class SupplyController extends Controller 
{ 
    // 补货控制器   
    public function test(Request $request){
        // 原生使用方法
        // $memcached = new \Memcached();
    	// $memcached->addServer('127.0.0.1',11211);
    	// $memcached->set('password',123456,900);
    	// $mem = $memcached->get('password');
    	// echo $mem;
        // $arr = [
         //   'name' => 'dongfan',
          //  'age' => 25,
          //  'sex' => 'male'
        //];
        //$arr = "'".json_encode($arr)."'";	
        //$arr = json_encode($arr);d
	//dd($arr);
	//$arr = 'zhangsan';        
        //Cache::store('memcached')->put('user',$arr,300);
        //$cur = Cache::store('memcached')->get('user');
	//$user = json_decode($cur,true);
        //dd($cur);
        Mail::send('supply.test',['name'=>'guangming'],function($message){
            $message->to('dongfanfan@ubox.cn');
        });

    }

    public function phpinfo(){
        phpinfo();
    }
    public function myTest(Application $app){
        //dd("dd");
        $supply = new SupplyService();
        $supply->sendSuccessNotifyToUser($app);

    }

    public function register(Request $request){
        $encrypt = $request->input('auth');
        
        $cdn_url = env('CDN_URL');
        return view('supply.register',[
                'cdn_url' => $cdn_url,
                'encrypt' => $encrypt
            ]);
    }

    public function registerResolve(Request $request){
        $name = $request->input('name');
        $phone = $request->input('phone');
        $encrypt = $request->input('encrypt');
        $openid = base64_decode($encrypt);
        if($name && $phone && $encrypt){
            Mail::send('supply.mail',[
                                        'name'=>$name,
                                        'phone'=>$phone,
                                        'openid'=>$openid
                                     ],function($message){
                                            $message->to('dongfanfan@ubox.cn')
                                                    ->subject($name.'发送的配送员申请邮件');
            });
        }
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
