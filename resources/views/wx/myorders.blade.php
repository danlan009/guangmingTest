<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
	<meta name="format-detection" content="telephone=no"/>
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta http-equiv="pragma" content="no-cache" />
	<title>我的订单</title>
	<link href="/sources/style/base.css" type="text/css" rel="stylesheet" />
</head>
<body class="grey">
<header>
	<h1><img src="/sources/images/common/logo.png" /></h1>
	<p>
		<a href="/wx/history" class="h">历史订单</a>
	</p>
</header>
<?php 
	$count = count($orders);
?>
<?php if( !$count ){ ?>
	<div class="history">
		<div class="empty">
			<p><img src="/sources/images/common/icon.png" /></p>
			<h3>暂无订单</h3>
			<a href="/wx/vmlist" class="blue_button">马上预定</a>
		</div>
	</div>
<?php } else { ?>
<?php 
	$orderCount = 0;
	$vmid = '';
?>
	<?php 
		foreach ($orders as $key => $order) { 
	        if(!$order){ continue; }
			$vms 		= $order['vms'];
			$user 		= $order['user'];
			$products 	= $order['products'];

			if($products){
				if($orderCount < 1){
	?>
					<div class="vminfor re">
						<h1>我的取货码：<mark><?php echo $user->password ?></mark></h1>
						<p>在售货机的屏幕上输入此密码即可取货</p>
					</div>
			<?php 
					$orderCount++;
				} 
			?>

			<?php if($vms->vmid !== $vmid){ ?>
				<div class="vminfor re o">
					<h1><?php echo $vms->vm_name ?><span>编号：<?php echo $vms->vmid ?></span></h1>
					<p><?php echo $vms->address ?></p>
				</div>
			<?php 
					$vmid = $vms->vmid;
				 } 
			?>

			<div class="myorders">
				<div class="status">
					<?php
						$statusArr = array('', '待配送', '配送中', '配送完成', '暂停中');
						echo $statusArr[$order['order_status']];
					?>
				</div>
				<div class="block a">
					<?php if($order->order_status == 1){ ?>
						从<?php echo str_replace('-', '.', $order['start_date']) ?>开始配送
					<?php }else{ ?>
						还可再配送 <mark><?php echo $order['other_date']['rest_date'] ?></mark> 天
					<?php } ?>
				</div>
				<div class="block b">
					<a href="/wx/order_details/<?php echo $order['id'] ?>">
					<?php foreach($products as $product){ ?>
						<section>
							<p><img src="<?php echo $product->img_url ?>" /></p>
							<h1><?php echo $product->product_name ?></h1>
							<h3><?php echo $product->volume.($product->unit == 1 ? 'ml' : 'g') ?></h3>
							<h2><mark>￥<?php echo round($product->retail_price/100, 2) ?></mark>×<span><?php echo $product->num ?></span>瓶</h2>
						</section>
					<?php } ?>
					</a>
				</div>
				<div class="block">
					<p>商品金额：<span>￥<?php echo round($order->total_price/100, 2) ?></span></p>
					<p>优惠卡券：<span><?php echo $order->card_name ? $order->card_name : '未使用优惠券' ?></span></p>
					<p>实付金额：<span class="red">￥<?php echo round($order->retail_price/100, 2) ?></span></p>
				</div>
				<div class="block">
					<ul>
						<li>订单编号：<?php echo $order->id ?></li>
						<li>下单时间：<?php echo str_replace('-', '.', $order->pay_time) ?></li>
						<li>配送时间：<?php echo str_replace('-', '.', $order['other_date']['start_date']).' - '.str_replace('-', '.', $order['other_date']['end_date']) ?>（<?php echo $order->rate ? '工作日' : '每天' ?>配送）</li>
						<li>联系电话：<?php echo $order->phone ?></li>
					</ul>
				</div>
				<div class="btn">
					
				<?php
					$stCount = count($order['stop']);
					if($stCount){
						$endDate = $order['stop'][$stCount-1]['end_date'];
						$startDate = $order['stop'][$stCount-1]['start_date'];
						$start = strtotime($endDate) + 24*60*60;
						
						// 已经恢复配送，还没到时间
						if($endDate !== '0000-00-00' && strtotime($endDate.' 23:59:59') > time()){
							echo '<p>';
							// echo '从<mark>'.str_replace('-', '.', $startDate).'</mark>暂停配送,';
							// echo  '<mark>'.Date('Y.m.d', $start).'</mark>恢复配送';
							echo '<mark>'.str_replace('-', '.', $startDate).'</mark>-<mark>'.str_replace('-', '.', $endDate).'</mark>暂停配送';
							echo '</p>';
							echo '<button class="blue_button">暂停配送</button>';
							echo '<select class="choosePauseDate" data-days="'.$order['other_date']['rest_date'].'" data-id="'.$order->id.'"></select>';
						}

						if($startDate && $endDate === '0000-00-00'){
							echo '<p>';
							echo '从<mark>'.str_replace('-', '.', $startDate).'</mark>暂停配送';
							echo '</p>';
							echo '<button class="blue_button btnContinueDelivery" data-days="'.$order['other_date']['rest_date'].'" data-start-date="'.str_replace('-', '.', $startDate).'" data-id="'.$order->id.'">恢复配送</button>';
						}
					}else{
						echo '<button class="blue_button">暂停配送</button>';
						echo '<select class="choosePauseDate" data-days="'.$order['other_date']['rest_date'].'" data-id="'.$order->id.'"></select>';
					}
				?>
				</div>
			</div>
			<?php } ?>
	<?php } ?>
<?php } ?>
<div class="maskA" id="maskA" style="display:none;">
	<p>操作成功，明天开始配送</p>
</div>
<script src="http://apps.bdimg.com/libs/zepto/1.1.4/zepto.min.js"></script>
<script src="/sources/scripts/ui.js" ></script>
<script type="text/javascript">
$(function(){

	$('.choosePauseDate').each(function(){
		var _me = $(this),
			orderId = _me.attr('data-id'),
			days 	= _me.attr('data-days');

		$.ajax({
			url: '/wx/ajax_get_dates',
			data: { days: days },
			type: 'get',
			success: function(d){
				// console.log(d);
				var data = JSON.parse(d),
					html = '<option value="">请选择停送日期</option>';
				// console.log(data);
				for(var i in data){
					html += '<option value="'+data[i]+'">'+data[i]+'</option>';
				}
				_me.html(html).show();
			}
		});

	});

	// 暂停配送
	$(document).on('change', '.choosePauseDate', function(){
		var _this = $(this),
			_date = _this.val(),
			orderId = _this.attr('data-id');

		$.ajax({
			url: '/wx/ajax_pause_delivery',
			data: {startDate: _date, orderId: orderId},
			type: 'get',
			success: function(d){
				console.log('订单'+orderId+',暂停配送');
				var parent = _this.parent('.btn'), html = '';
				html = "<p>从<mark>"+_date+"</mark>开始暂停配送</p>";
				html += '<button class="blue_button btnContinueDelivery">恢复配送</button>'
				// parent.siblings('.status').text('暂停中');
				parent.html(html);
			}
		});
		return false;
	});

	// 恢复配送
	$(document).on('click', '.btnContinueDelivery', function(){
		// console.log('恢复配送')
		var _me = $(this),
			orderId = _me.attr('data-id'),
			days 	= _me.attr('data-days');
		console.log('恢复配送')
		console.log('订单编号:'+orderId);

		$.ajax({
			url: '/wx/ajax_continue_delivery',
			data: {orderId: orderId},
			type: 'get',
			success: function(d){
				console.log(d);
				if(d=='1'){
					var _btn = _me.parent('.btn'),
						_status= _btn.siblings('.status'),
						_start = _me.attr('data-start-date'),
						_end = '';
						html = '';

					_end = new Date();
					_end = _end.getFullYear()+'.'+(_end.getMonth()+1)+'.'+_end.getDate();

					html += '<p><mark>'+_start+'</mark>-<mark>'+_end+'</mark>暂停配送</p>';
					html += '<button class="blue_button btnStopDelivery" data-days="'+days+'" data-id="'+orderId+'">暂停配送</button><select></select>';

					_btn.html(html);
					var mask = $('#maskA');
					// _status.text('配送中');
					mask.show();
					setTimeout(function(){
						mask.hide();
					}, 2000);
				}
			}
		});
	});
});


// var _end = new Date();
// 	_end = _end.getFullYear()+'.'+(_end.getMonth()+1)+'.'+_end.getDate();
// console.log(_end);
</script>
</body>
</html>