<?php 
namespace App\Lib;

use App\Business\ActService;

class ThUtil{
	public static function dayBetween($d = false, $retInt = false){
		$d		= $d ? $d : date('Y-m-d');
		$r		= array($d.' 00:00:00', $d.' 23:59:59');
		if($retInt) return array(strtotime($r[0]), strtotime($r[1]));
		else return $r;
	}
	
	public static function uboxIpAuth($exi = true){
		$ip = $_SERVER['REMOTE_ADDR'];
		if(!in_array($ip, array('1.202.249.162', '106.39.95.2', '211.151.164.51', '127.0.0.1', '211.151.164.58', '211.151.164.119', '211.151.164.59', '1.202.249.162', '106.39.95.4', '192.168.11.70'))){
			if($exi){
				\Log::info('【uboxIpAuth】 error:'.$ip);
				exit('error ip');
			}
			return false;
		}
		return true;
	}
	
	public static function actChannel($actName, $vmid){
		$c 		= '';
		if(\Request::has('channel')){
			$c	= \Request::get('channel');
		}else if($actName == 'coupon_201505'){
			if($vmid == '0' || $vmid == '99999'){
				if(\Request::has('pub_name') && \Request::has('pub_name') == 'visi'){
					$c	= 'airport';
				}else{
					$c	= 'subway';
				}
			}
			if($vmid == '99999' && !\Request::has('ticket')){
				$c .= '-qr';
			}
		}else if($actName == 'mobile_inte_201506'){
			if($vmid == '0' && \Request::has('pub_name')){
				if(\Request::has('pub_name') == 'gift'){
					$c	= 'subway';
				}else if(\Request::has('pub_name') == 'visi'){
					$c	= 'airport';
				}
			}
			if($vmid == '99999' && !\Request::has('ticket')){
				$c .= '-qr';
			}
		}
		return $c;
	}
	
	public static function arrVal($arr, $key, $dv = ''){
		return empty($arr[$key]) ? $dv : $arr[$key];
	}
	
	public static function buildExcel($path, $dataArray){
		$da = json_encode($dataArray);
		\Log::info('【buildExcel】 0, | data length:'.strlen($da));
		$xls = Http::curlPost('http://'.\Config::get('th.host').'/tool/php_excel.php', array(
			'file_path'	=> $path,
			'data_arr'	=> $da
		));
		if(!$path) return $xls;
		$r = file_put_contents($path, $xls);
		\Log::info('【buildExcel】 1, | $path:'.json_encode($path).' | $dataArray:'.json_encode($dataArray));
		\Log::info('【buildExcel】 2, | xls length:'.strlen($xls).'  file_put_contents return('.$r.')');
		return $r;
	}
	//发邮件
	public static function sendMail($to, $title, $body){
		$r = Http::curlPost('http://push.uboxol.com/send_mail', array(
			'to'	=> $to,
			'title'	=> $title,
			'body'	=> $body,
		));
		\Log::info('【mail】 | $to:'.json_encode($to).' | $title:'.$title.' | $body length:'.strlen($body).'  return('.$r.')');
		return $r;
	}
	//发邮件
	public static function sendMail4Atta($to, $title, $body, $attas = array()){
		if(!class_exists('PHPMailer')){
			require_once __DIR__.'/phpMailer/class.phpmailer.php';
		}
		$mail = new PHPMailer();
	//	$mail->Host = 'smtp.exmail.qq.com';
	    $mail->smtp="localhost";
	    $mail->IsSMTP();
	//	$mail->Host = 'smtp.sina.com';
	    $mail->Host = 'smtp.qq.com';
		$mail->CharSet = 'utf-8';
		$mail->SMTPDebug = false;
		$mail->SMTPAuth = true;
	//	$mail->SMTPSecure = 'ssl';
	//	$mail->Port = 465;
		$mail->Port = 25;
		$mail->From = 'liuqin871252072@sina.com'; //发件人的邮箱
	//	$mail->Username = 'thonline-stat@ubox.cn';
	//	$mail->Password = 'Qb8whKgabfJ%FU$2JZxQ';
		$mail->Username ='liuqin871252072@sina.com';
		$mail->Password = 'love4097';
		$mail->FromName = 'TH-统计';
		$mail->Sender	= 'th-online';
		//$email->AltBody = 'text/html';
		if(is_string($to)) $to = explode(';', $to);
		foreach($to as $tovar){
			$mail->AddAddress ($tovar,"收件人");
		}
//		$mail->AddAddress ('jiangkunlun@ubox.cn');
		if(!empty($attas)){
			foreach($attas as $fpath => $fname){
				$mail->AddAttachment($fpath, $fname);
			}
		}
		$mail->Subject = $title;
		$mail->IsHTML (true);
	//	$mail->MsgHTML ($body);
		$mail->Body = $body;
		$r = $mail->send();
		\Log::info('【mail】 | $to:'.json_encode($to).' | $title:'.$title.' | $body length:'.strlen($body).'  return('.$r.')');
		return $r;
	}
	//返回各种验证码，不区分验证码
	//$length 验证码的位数
	//$type 0 纯数字  1 纯字母  2字母加数字 
	public static function getVerifyCode($length = 5, $type = 0){
		$patterns = array(
			'1234567890',
			'ABCDEFGHIJKLOMNOPQRSTUVWXYZ',
			'1234567890ABCDEFGHIJKLOMNOPQRSTUVWXYZ',
			'1234567890ABCDEFGHIJKLOMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'
		);
		$key = '';
		for($i=0; $i<$length; $i++){
   			$key .= $patterns[$type]{mt_rand(0, strlen($patterns[$type]) - 1)};    //生成php随机数
 		}
 		return $key;
	}
	//获取时间差
	public static function lt($sTime, $now = false){
		if(!$now) $now = explode(' ', microtime());
		return number_format((($now[1] - $sTime[1]) + ($now[0] - $sTime[0]))  * 1000, 3);
	}
	//将分变成元 
	public static function fen2yuan($v){
		$v = $v - 0;
		$v = round($v);
		$v .= '';
		if(strlen($v) == 2) $v = '0'.$v;
		else if(strlen($v) == 1) $v = '00'.$v;
		return substr($v, 0, strlen($v) - 2).'.'.substr($v, -2, 2);
	}
	//将字符串的元，变成数字分
	public static function yuan2fen($v){
		$v 		= $v.'';
		$v2		= explode('.', $v);
		$r		= ($v2[0] - 0) * 100;
		$r2		= empty($v2[1]) ? '0' : $v2[1];
		if(strlen($r2) > 3) $r2	= substr($r2, 0, 3);
		else if(strlen($r2) == 1) $r2 = $r2.'0';
		$r2		= $r2 - 0;
		if($r2 > 100){
			$r2	= round($r2 / 10);
		}
		return $r + $r2;
	}
	//提取request的参数
	public static function reqVal($key, $defVal = ''){
		return \Request::has($key) ? \Request::get($key) : $defVal;
	}

	public static function strToArr($str){
		$str = substr($str,0,strlen($str)-1);
		$arr = explode(';',$str);
		$data = array();
		foreach( $arr as $i ){
			$param = explode('=',$i,2);//最多分割2个数组
			$data[ trim($param[0]) ] = trim(str_replace('"','',$param[1]));
		}
		return $data;
	}
	//'10号线公主坟站西南厅西南口闸内电梯口0024223'
	public static function checkVmid($str){
		preg_match('/([0-9]{7})/', $str, $a);
		\Log::info('【checkVmid】'.$str.'=>'.json_encode($a));
		return empty($a[0]) ? false : $a[0];
	}
	
	public static function moreActs($channel, $vmid = false, $vmAddr = ''){
		$gets			= '?acts=1&pub_name='.(!\Session::has('pub_name') ? 'gift' : \Session::get('pub_name')).'&channel='.$channel.(\Request::has('ticket') ? '&ticket='.\Request::get('ticket') : '');
		if(!$vmid && \Session::has('beacon_data')){
			$vmid		= (new ActService())->loadVmid4Beacon(\Session::get('beacon_data'), false, $vmAddr);
		}
		if($vmid) $gets.= '&vmid='.$vmid;
		if($vmAddr) $gets.= '&vm_addr='.urlencode($vmAddr);
		$urlCoupon 		= 'http://wx.tahovending.com/subway/coupon/'.(empty($vmid) ? 99999 : $vmid).$gets;
		$urlMobileInte 	= 'http://wx.tahovending.com/subway/mobile_inte_201506/'.(empty($vmid) ? 99999 : $vmid).$gets;
		$actUrls		= array(
			'url_coupon'		=> $urlCoupon,
			'url_mobile_inte'	=> $urlMobileInte,
		);
		//售卖
		if($channel == 'chongqing_sub_sale'){
			$actUrls['url_sale']	= 'http://wx.tahovending.com/vm/zb/'.$vmid.$gets;
		}
	//	$actUrls['url_wuxianjin'] = 'https://wx.gtimg.com/payact/2015/ncd/index.html?token=3a65c5a3b118d777b12754aa933dfec37c0819d63493d9db410554942a01285cde3e8503dfae78b55e78afcb79490e1deedd332ab5e59bb9b1431a010dafc4da67692140b0b5856a6f2f5dede7f2db9f3214c690a6c08abdd1060218b4524e4c2dc79de799bcc2816cc5326b1b96cb74fb7fc6eecfea485be0c8aca8ccc727679a87285e053b4908be0ab47e830d98d6c6a680838cf365a5d9f495b19e1df6a2c32f841e24a7b318d33a5aea78feccd6a44075802257279704d59e5a2b82f4207e52bc83498f4dbae774267d5610c74e16959aef2f5459b38a7cd3f215cf96e56d9de4550271438db7ecb1a948fde40fba4ce9781b6e4af972db468aee1f1f47741aaa615606d532788acf6d2199a8a3dd46028a884f17318f771bd61abca7be&appid=wx57849631bb367f52&activity_code=nocashday_1&appid=wx57849631bb367f52&channel_code=wxmessage&sign=743a956bbd72743e9c36ea1e8fce2c9b&src=wx_nocashday';
		//杭州地铁，有赞判断  2015年08月27日11:15:23 下线 by 丹丹、堂全
// 		$youzanVms		= array('0026001','0021379','7100341','7100342','7100343','7100345','7100346','7100347','7100348','7100349','7100538','7100631','7100632','7100633','7100661','7100665','7100666','7100695','7100767','7100768','7100800','7100801','7100809','7100810','7100812','7100813','7101180','7101183','7101250','7101251','7101252','7101312','7101354','7101355','7101356','7101996','7101997','7101998','7101999','7102000','7102001','7102002','7102003','7102005','7102006','7102007','7102008','7102009','7102010','7102011','7102012','7102013','7102015');
// 		if($channel != 'chongqing_sub_sale' && !empty($vmid) && in_array($vmid, $youzanVms)){
// 			$actUrls['url_youzhan']	= 'http://wx.tahovending.com/subway/youzhan'.$gets;
// 		}
		//国元证券 2015年08月27日14:16:33 下线 by 佟鑫
// 		$guoyuanVms		= array('0023263','0023134','0023368','0023383','0023370','0023302','0020070','0023219','3210271','3212697','3212100','3212101','3212160','3210263','3212031','3211678','3212079','3212634','3211666','3212145','3212626','3212623','3212495','3212579','3212109','3212045','3212643','3211682','3211700','3212646','3212077','3212644','3211671','3212146','3212147','3211680','3212645','3212055','3212156','3212162','3212163','3212164','3212165','3212166','3212576','3212638','3212639','3212640','3212054','3212167','3212148','3211693','3211677','3212018','3210273','3211692','3212017','3212110','3210275','3212103','3212104','3212144','3211674','3211669','3212048','3211679','3211697','3212015','3212019','3212060','3212053','3210269','3210270','3211564','3212105','3212149','3212154','0220028','0220029','0220027','0220076','0220079','0220075','0220036','0220037','0220035','0220031','0220032','0220030','0220007','0220015','0220006','0220017','0220018','0220016','0220062','0220065','0220061','0220046','0220050','0220045','0220039','0220040','0220038','0220025','0220026','0220023','0220020','0220021','0220019','0220003','0220005','0220002','0220059','0220063','0220058','0220010','0220012','0220009','0220073','0220078','0220072','0220042','0220043','0220041','221768','221769','221766','221767','221771','221772');
// 		if($channel != 'air_zb' && !empty($vmid) && in_array($vmid, $guoyuanVms)){
// 			$actUrls['url_guoyuan']	= 'http://ubox-act.ubox.cn/guoyzq/gyzq?thonline=1&vmid='.$vmid;
// 		}
		//2015年08月26日15:32:36 微信演示
		//2015年08月28日14:23:34 微信大学活动
		if($channel == 'wx_university'){
			$actUrls		= array(
				'url_wx_dianping'	=> 'http://evt.dianping.com/bonus/yyy/bonus.html?utm_source=yb08',//'http://evt.dianping.com/bonus/qixibonus3/bonus.html?utm_source=youbao0819',
				'url_wx_hongbao'	=> 'http://b.wepiao.com/hongbao/index.html?pid=%40%F1%11%A1%0D%B1%96%D3&channelid=3&chid=100&val_id=%D3%D8%C9%BC%F4%1D6%B3',
				'url_mobile_inte'	=> $urlMobileInte,
// 				'url_sale'			=> 'http://wx.tahovending.com/vm/zb/'.$vmid.$gets
			);
			//view
			$actUrls['more_act_view'] = 'wx_university_more_act';
		}
		//2015年09月06日15:56:12 机场 aircar
		if($channel == 'air_zb'){
			$actUrls['url_aircar']	= 'http://wx.tahovending.com/airport/aircar'.$gets;
		}
		//2015年09月10日16:24:16 厦门展会
		if($channel == 'xiamen_2015'){
			$actUrls		= array(
				'url_fight_maidong'	=> 'http://wx.tahovending.com/airtravel/complimentary'.$gets,
				'url_mobile_inte'	=> $urlMobileInte,
			);
			if(\Request::has('xiamen_2015') && \Request::get('xiamen_2015') == 'A'){
				$actUrls['url_xiamen_photo']		= 'http://wx.tahovending.com/airport/xiamen_photo'.$gets;
			}
			$actUrls['page_title']	= '手机贵宾厅 丰富出行体验';
		}
		\Log::info('【moreActs】vmid:'.$vmid.', beacon_data:'.(\Session::has('beacon_data') ? json_encode(\Session::get('beacon_data')) : 'no').', act count:'.count($actUrls).', acts:'.json_encode(array_keys($actUrls)));
		return $actUrls;
	}
	//0 初始  1 用户确认下单  3 无货/商品售空，或其他下单失败  5 正在支付  6 支付成功  7 支付失败   8 需要退款   9 正在退款 微信支付， 支付、退款的详细信息，在trade表里，出货的详细状态在deliver_status字段  
	//11 订单完成：成功交易  12 订单完成：成功退款  14 退款失败
	//出货状态，1 买码成功，可以出货  2 买码失败   5 出货成功  6  出货失败  7  出货结果确认中  3 支付成功，可以买码
	//$type = 1, 给用户看的  $type = 2, 给内部人看的
	public static function orderStatusView($order, $type = 1, $print = false){
		$status 		= $order->status;
		$deliverStatus 	= $order->deliver_status;
		$business		= $order->business;
		$v				= array('新建订单', '新建订单');
		switch($status){
			case 0:	//初始
				$v			= array('新建订单', 			'新建订单');
				break;
			case 1:	//
				$v			= array('新建订单', 			'用户确认下单');
				break;
			case 3:	//
				$v			= array('已售空', 			'无货/商品售空/断网，或其他下单失败');
				break;
			case 5:	//
				$v			= array('等待支付	', 			'用户准备支付',			'点击支付按钮');
				break;
			case 6:	//
				$v			= array('支付完成，正在出货', 	'支付成功');
				switch($deliverStatus){
					case 1:
						$v	= array('正在出货', 			'买码成功，可以出货');
						break;
					case 2:
						$v	= array('出货失败，正在为您退款','买码失败');
						break;
					case 3:
						$v	= array('正在出货', 			'支付成功，可以买码');
						break;
					case 5:
						$v	= array('出货成功', 			'买码成功，可以出货');
						break;
					case 6:
						$v	= array('出货失败，正在为您退款','出货失败');
						break;
					case 7:
						$v	= array('正在确认出货结果',		'出货结果确认中');
						break;
				}
				break;
			case 7:	//
				$v		= array('等待支付', 				'支付失败');
				break;
			case 8:	//
				$v		= array('正在为您退款', 			'准备退款');
			case 9:	//
				$v		= array('正在为您退款', 			'正在退款');
				break;
			case 11:	//
				$v		= array('购买成功	', 				'订单完成：成功交易',		'购买成功');
				break;
			case 12:	//
				$v		= array('已退款', 				'订单完成：成功退款',		'购买失败，已退款');
				break;
			case 14:	//
				$v		= array('退款失败，请联系客服', 		'退款失败');
				break;
				
		}
		$type_	= $type - 1;
		return empty($v[$type_]) ? $v[$type_ - 1] : $v[$type_];
	}
	
	public static function send2Rebot($msg){
		$url 	= 'http://th-online.dev.uboxol.com/rebot/timeline/send_thing?sou=thonline&msg='.urlencode($msg);
		$r 		= Http::curlGet($url, 0.05, $err);
		\Log::info('【send2Rebot】$msg:'.$msg.', return:'.$r.', error:'.$err);
	}

	public static function getDirImg($productId){
		$cdnPath = "http://".\Config::get('ab.host')."/file_img/";
		return $cdnPath.$productId."/dir.jpg";
	}

	public static function listDetailImgs($productId){
		$cdnPath = "http://".\Config::get('ab.host')."/file_img/";
		$dir = public_path()."/file_img/{$productId}/";
		$rt = array();
		if(!file_exists($dir)){
			\Log::error("Product img dir does not exists: $dir");
			return $rt;
		}
		$files = scandir($dir);
		\Log::debug("Product dir $dir has files: ".json_encode($files));
		foreach ($files as $i => $filename) {
			$lower = strtolower($filename);
			if(strpos($lower, "dir")===0){
				continue;
			}elseif (strpos($lower, "jpg")>0 || strpos($lower, "png")>0 || strpos($lower, "gif")>0) {
				$rt[$filename] = $cdnPath.$productId."/".$filename;
			}
		}
		if(count($rt) > 0){
			ksort($rt, SORT_NUMERIC);
			$rt = array_values($rt);
		}
		\Log::info('【listDetailImgs】$productId:'.$productId.', return:'.json_encode($rt));
		return $rt;
	}

    public static function cardNameList(){
        return array(
            // 'pNwdztz7IfeANABIvSCJ-cjA5Vak' => array(	//以下优惠券正式投放
            //     'name' 	=> '好奇而已全场减10元',
            //     'type' 	=> '2',
            //     'value' => '1000'
            // ),
            // 'pNwdzt5cwUZYBvlVayTlJgSPQFss' => array(
            //     'name' 	=> '好奇而已8.5折券', 
            //     'type' 	=> '1',
            //     'value' => '85'
            // ),

            'pNwdzt5UPPiW1TAR9ljFF3Z7DDsM' => array( //以下优惠券正式投放
                'name' 		=> '满20元减免18元', 
                'type' 		=> '2',
                'value' 	=> '2000',
                'discount' 	=> '1800'
            ),
            'pNwdzt1VhQCH1z9oDNOpVssQIFwY' => array(
                'name' 		=> '满20元减免18元', 
                'type' 		=> '2',
                'value' 	=> '2000',
                'discount' 	=> '1800'
            ),
            'pNwdztzrbKUlS1vph9dq66VLq_30' => array(
                'name' 		=> '满200元减免40元',
                'type' 		=> '2',
                'value' 	=> '20000',
                'discount' 	=> '4000'
            ),
            'pNwdzty0vx-nF_QNQwDv4TEqXipg' => array(
                'name' 		=> '满100元减免20元',
                'type' 		=> '2',
                'value' 	=> '10000',
                'discount' 	=> '2000'
            ),
            'pNwdztyH1dYLXbD8Wky1vD-Am-1A' => array(
                'name' 		=> '满150立减20',
                'type' 		=> '2',
                'value' 	=> '15000',
                'discount' 	=> '2000'
            ),
            'pNwdztxULYjJmVw3Jrp4Z5jfInNU' => array(
                'name' 		=> '满300立减50',
                'type' 		=> '2',
                'value' 	=> '30000',
                'discount' 	=> '5000'
            ),
            'pNwdztx0FstYITNQ283a_8nMXmBI'	=> array(
            	'name' 		=> '满100立减5元',
                'type' 		=> '2',
                'value' 	=> '10000',
                'discount' 	=> '500'
            ),
            'pNwdzt6W5wk243eLGJfM0ZgiV9hI'	=> array(
            	'name' 		=> '满300元立减20元',
                'type' 		=> '2',
                'value' 	=> '30000',
                'discount' 	=> '2000'
            ),
            'pNwdzt1VQLyO9mxhwBiNWNSCxlP8'	=> array(
            	'name' 		=> '200元立减15元',
                'type' 		=> '2',
                'value' 	=> '20000',
                'discount' 	=> '1500'
            ),
            'pNwdztw7Osk-iSQ2ppBoniyLTkjs'	=> array(
            	'name' 		=> '满150元立减12元',
                'type' 		=> '2',
                'value' 	=> '15000',
                'discount' 	=> '1200'
            ),
            'pNwdzt7-RmBToBNFC2KT5cC7eM5k'	=> array(
            	'name' 		=> '满100立减8元',
                'type' 		=> '2',
                'value' 	=> '10000',
                'discount' 	=> '800'
            ),
            'pNwdzt_b8ZepTROZK9YxGDRqNpUQ'	=> array(
            	'name' 		=> '满28立减5元',
                'type' 		=> '2',
                'value' 	=> '2800',
                'discount' 	=> '500'
            ),
            'pNwdzty3EW6mE8qILccxnDwa5Dpc'	=> array(
            	'name' 		=> '满288元立减68元',
                'type' 		=> '2',
                'value' 	=> '28800',
                'discount' 	=> '6800'
            ),
            'pNwdzt9eozn_HtglygNmKTWI_6yI'	=> array(
            	'name' 		=> '满188元立减38元',
                'type' 		=> '2',
                'value' 	=> '18800',
                'discount' 	=> '3800'
            ),
            'pNwdzt-xMa68dF21T7rYdl6-QRQc'	=> array(
            	'name' 		=> '满88立减18元',
                'type' 		=> '2',
                'value' 	=> '8800',
                'discount' 	=> '1800'
            )
        );
    }

    public static function isTesterWeiXin($userId){
    	$testers = array(
    			721903, 	22932633, 	4940964, 	418449, 	29105096, 	3662035, 	22592014, 	 
    			42766440, 	42770392, 	42753618, 	42814447, 	42764667, 	14328714, 	709
    		);
    	return in_array($userId, $testers);
    }

    public static function testVmsInfor(){
    	$vmsInfor = array();
		$vmsInfor['9999997'] = array(
				'id'		=> '9999997',
				'vmName'	=> '公司旧工控升级测试',
				'address'	=> '上海市闵行区浦江镇联航路1188号1号楼东四楼',
				'lat' 		=> 31.085073,
				'lng'		=> 121.53139,
				'newName' 	=> '好奇而已旗舰店',
				'isShop' 	=> '1'
			);
		$vmsInfor['9990331'] = array(
				'id'		=> '9990331',
				'vmName'	=> 'U75测试使用1',
				'address'	=> '上海市闵行区浦江镇联航路1188号1号楼东四楼',
				'lat' 		=> 31.085073,
				'lng'		=> 121.53139,
				'newName' 	=> '公司测试02',
				'isShop' 	=> '1'
			);
		return $vmsInfor;
    }
}

























