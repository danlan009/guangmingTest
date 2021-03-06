<?php
namespace App\Service;

use App\Model\Order; 
use App\Model\OrderLog;
use App\Model\OrderStop; 
use App\Model\Sku;
use App\Model\SkuSupply;
use EasyWeChat\Foundation\Application;
use DB;   
use Cache;  
use Log;

use App\Service\StatService;
class MallService{
	// 拉取点位列表 参数:无
	public function getNodeList(){
		$nodes = DB::table('nodes')
					->join('vms','nodes.id','=','vms.node_id')
                    ->select('nodes.id','nodes.node_name','nodes.address','nodes.address','nodes.lng','nodes.lat','vms.vmid','vms.vm_name')
                    ->get();

        // 格式化数据
        $nodeList = [];
        foreach ($nodes as $node) {
            if(in_array($node->id,$nodeList)){
                $nodeList[$node->id]['vms'][$node->vmid]['vmid'] = $node->vmid;
                $nodeList[$node->id]['vms'][$node->vmid]['vm_name'] = $node->vm_name;
            }else{
                $nodeList[$node->id]['id'] = $node->id;
                $nodeList[$node->id]['node_name'] = $node->node_name;
                $nodeList[$node->id]['address'] = $node->address;
                $nodeList[$node->id]['lng'] = $node->lng;
                $nodeList[$node->id]['lat'] = $node->lat;
                $nodeList[$node->id]['vms'][$node->vmid]['vmid'] = $node->vmid;
                $nodeList[$node->id]['vms'][$node->vmid]['vm_name'] = $node->vm_name;
            }
        }
        Log::debug('MallService--getNodeList--returns::'.json_encode($nodeList));
        return $nodeList;
	}

    // 根据vmid 获取售货机相关信息
    // 参数:vmid
    public function getVmInfo($vmid){
        $vmInfo = DB::table('vms')
                        ->join('nodes','vms.node_id','=','nodes.id')
                        ->where('vms.vmid',$vmid)
                        ->select('vms.vmid','vms.vm_name','nodes.id as node_id','nodes.node_name','nodes.address','nodes.lng','nodes.lat')
                        ->get();
        if(empty($vmInfo)){
            return 0; //未找到售货机
        }
        Log::debug('MallService---getVmInfo returns::'.json_encode($vmInfo));
        return get_object_vars($vmInfo[0]);
    }
	// 根据vmid 拉取所有商品
	// 参数:1.vmId 售货机id 
    //      2.type 'sale':即卖
    //             'book':预定

    public function showPros($vmid , $type){
        if(!isset($vmid) || !isset($type)){
            return 'parameter missing!';
        }

        $proList = Sku::getAllPros($vmid);
        // Log::debug('MallService::getAllPros---'.json_encode($proList));
 
        // 放入缓存
        $counts = []; // 用于商品列表根据剩余量排序
        foreach ($proList as $k=>$pro) {
            $pid = $pro->product_id; 
            if($type == 'sale'){ 
                $num = $this->getNumOfSale($vmid,$pid); //计算即卖商品剩余数量
                $pro->count = $num;    
                $counts[] = $num;
            }else{
                $num = $this->getNumOfBook($vmid,$pid); //计算预定商品剩余数量
                Log::debug('getNumOfBook---returns---'.$num);
                $pro->count = $num;
                $counts[] = $num;
            }

            $pro->pic_l = StatService::getImg('products',$pid,'l'); // 商品列表展示图
            $pro->pic_t = StatService::getImg('products',$pid,'t'); // 商品详情大图
            $pic_d1 = StatService::getImg('products',$pid,'d1'); // 商品详情描述图1
            $pic_d2 = StatService::getImg('products',$pid,'d2'); // 商品详情描述图2
            $pic_d3 = StatService::getImg('products',$pid,'d3'); // 商品详情描述图3
            $pro->detail_pics = [
                "$pic_d1",
                "$pic_d2",
                "$pic_d3"  
            ];

            Cache::put('PRO_DETAIL_'.$proList[$k]->product_id.'_'.$vmid,$proList[$k],1440);
        }
        // Log::debug('MallService::showPros to '.$type.' put in Cache---'.json_encode($proList));
        array_multisort($counts,SORT_DESC,$proList);
        return $proList;
    }

    // 获取某售货机下商品详情
    // 参数1.pid 商品id 2.vmId 3.type:'sale/book'
    public function getProDetail($pid,$vmid,$type){
        if(empty($pid) || !isset($vmid) || !isset($type)){
            return 'parameter missing!';
        }
        $proDetail = Cache::get('PRO_DETAIL_'.$pid.'_'.$vmid);
        Log::debug('MallService::getProDetail get data from Cache---'.json_encode($proDetail));
        if(empty($proDetail)){ //需要重新拉取售货机商品列表,放入缓存
           $list = $this->showPros($vmid,$type);
           $proDetail = Cache::get('PRO_DETAIL_'.$pid.'_'.$vmid);
           Log::debug('MallService::getProDetail after showPros get data from Cache---'.json_encode($proDetail));
        }
        if(!empty($proDetail)){
            return $proDetail;
                  
        }else{
            return null;
        }
    }

    // 计算某台售货机下即卖商品剩余数量
    public function getNumOfSale($vmid,$product_id){
        // 计算售货机中该商品总数量
        $totalNum = DB::table('skus')
                        ->join('sku_supplys as skps','skus.id','=','skps.sku_id')
                        ->where('skus.vmid',$vmid)
                        ->where('skus.product_id',$product_id)
                        ->where('skps.status',2)
                        ->count();
        // 计算售货机中已下单商品(包括预定和即卖)
        $bookNum = DB::table('orders')
                        ->join('order_logs','orders.id','=','order_logs.order_id') 
                        ->where('orders.order_status',2)
                        // ->where('channel',1)
                        ->where('orders.vmid',$vmid)
                        ->where('order_logs.product_id',$product_id)
                        ->count();
        $num = $totalNum - $bookNum;
        return $num;
    }

    // 计算某台售货机可以预定商品剩余数量
    public function getNumOfBook($vmid,$product_id){
        $skuList = DB::table('skus')
                        ->where('vmid',$vmid)
                        ->where('product_id',$product_id)

                        ->select('id','sku_size')
                        ->get();
        $max = 0;
        foreach ($skuList as $sku) {
            $max += $sku->sku_size;
        }
        
        // 配送中且第二天仍不会结束
        $existCount = DB::table('orders')
                        ->join('order_details as ods','orders.id','=','ods.order_id')
                        ->where('orders.channel',1)
                        ->where('orders.order_status','<>',3)
                        ->where('orders.vmid',$vmid)
                        ->where('ods.product_id',$product_id)
                        // ->get();
                        ->count();        
    
        $num = $max - $existCount;
        return $num;
    }
 
    // 即卖生成单个取货码(下单后操作)
    public function singleBuyCode($order_id,$order_detail_id,$product_id,$vmid){ // 预下单后买码
        
        // 校验是否有商品可供继续买码 
        $availCount = $this->getNumOfSale($vmid,$product_id);
        if($availCount > 0){
            $blno = $this->createBlno();
            $date = date('Y-m-d');
            // 检验是否重复(当天,同一台取货机不能有重复取货码)
            $exists = OrderLog::where('vmid',$vmid)->where('create_date',$date)->pluck('blno')->toArray();

            while(in_array($blno,$exists)){
                $blno = $this->createBlno();
            }

            $model = new OrderLog();
            $model->order_id = $order_id;
            $model->order_detail_id = $order_detail_id;
            $model->product_id = $product_id;
            $model->create_date = $date;
            $model->order_status = 201;
            $model->is_reserved = 0;
            $model->blno = $blno;
            $model->vmid = $vmid;
            $res = $model->save();
            Log::debug('single_buy_code successfully---returns::'.'order_id:'.$order_id.'order_detail_id:'.$order_detail_id.'product_id:'.$product_id.'blno:'.$blno);
            if($res){
                return ['return_code'=>'200','return_msg'=>''];
            }
        }else{
            return ['return_code'=>'410','return_msg'=>'该商品已被买走'];
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

    public function sendExpireProduct(Application $app){
        $expire_product_list = SkuSupply::getExpireProduct();
        $notice = $app->notice;
        //dd($expire_product_list);
        //获得配送员的配置信息
        $string = file_get_contents(public_path().'\file_img\json\sender.json');
        $obj = json_decode($string);
        $infos = $obj->deliverymen;
        //先按照普通公众号的推送消息
        $templateId = '';
        $i=0;
        $arr = array();
        //组合数据
        if(count($expire_product_list)>0){
            $res = array();
            foreach ($infos as $info){
                $res[$info->wx_id][]=$info;
                foreach ($res[$info->wx_id] as $resF){
                    foreach ($expire_product_list as $v){
                        foreach ($resF->vms as $k=>$vms){
                            if($v->vmid==$vms) {
                                $resF->vms[$vms][]=$v;
                                //unset($resF->vms[$k]);
                            }else{

                            }
                        }
                    }
                }
            }

            //字符串拼接好后向其发送消息
            foreach ($res as $k=>$v){
                $openid = $k;
                $url = '';
                $data = array();
                //foreach循环取出
                $arr[$i] = json_decode($notice->uses($templateId)->withUrl($url)->andData($data)->andReceiver($openid)->send());
                $i++;
            }


        }
        return $arr;
    }

    
}
?>