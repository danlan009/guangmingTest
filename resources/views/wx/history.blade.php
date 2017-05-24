<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
	<meta name="format-detection" content="telephone=no"/>
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta http-equiv="pragma" content="no-cache" />
	<title>历史订单</title>
	<link href="/sources/style/base.css" type="text/css" rel="stylesheet" />
</head>
<body class="grey">
<?php 
	define("PAGENUMBER", 10); // 每页显示商品数量
	$count = count($orders);
?>
<?php if(!$count){ ?>
<header>
	<h1><img src="/sources/images/common/logo.png" /></h1>
	<p>
		<a href="/wx/history" class="h">历史订单</a>
	</p>
</header>
<div class="empty">
	<p><img src="/sources/images/common/icon.png" /></p>
	<h3>暂无订单</h3>
	<a href="/wx/vmlist" class="blue_button">马上预定</a>
</div>
<?php }else{ ?>
	<?php foreach ($orders as $key => $order) { ?>
		<?php 
			$products = $order['products'];
			$channel = '1'; // 即时售卖
			if($order['channel'] == 1 || $order['channel'] == 2){ 
				$channel = '2'; // 预定
			}
		?>
		<div class="myorders <?php $key < PAGENUMBER ? 'show' : '' ?>">
			<div class="status">
				<?php 
					// 订单状态 1待配送，2配送中，3配送完成 ,4暂停
					$statusArr = ['', '待配送', '配送中', '已完成', '暂停中'];
					echo $statusArr[$order['order_status']];
				?>
			</div>
			<div class="block a">
				<?php if($channel === '2'){ ?>
					【预定订单】共配送 <mark><?php $order['type'] ?></mark> 天
				<?php }else{ ?>
					【在线购买】
				<?php } ?>
			</div>
			<div class="block <?php echo $channel === '1' ? 'd' : 'b' ?>">
				<a href="/wx/order_details/<?php echo $order['id'] ?>">
					<?php foreach($products as $p){ ?>
						<section>
							<p><img src="<?php echo $p->img_url ?>" /></p>
							<h1><?php echo $p->product_name ?></h1>
							<h3><?php echo $p->volume.($p->unit === 1 ? 'ml' : 'g') ?></h3>
							<h2><mark>￥<?php echo round($p->retail_price/100, 2) ?></mark>×<span><?php echo $p->num ?></span>瓶</h2>
						</section>
					<?php } ?>
				</a>
			</div>
			<div class="block">
				<?php if($channel === '2'){ ?>
					<p>商品金额：<span>￥<?php echo round($order['total_price']/100, 2) ?></span></p>
				<?php } ?>
				<p>优惠卡券：<span><?php echo $order['card_name'] ? $order['card_name'] : '未使用优惠券' ?></span></p>
				<p>实付金额：<span class="red">￥<?php echo round($order['retail_price']/100, 2) ?></span></p>
			</div>
			<div class="block">
				<ul>
					<li>订单编号：<?php echo $order['id'] ?></li>
					<li>下单时间：<?php echo str_replace('-', '.', $order['pay_time']) ?></li>
					<?php if($channel === '2'){ ?>
						<li>配送时间：<?php echo str_replace('-', '.', $order['other_date']['start_date']).' - '.str_replace('-', '.', $order['other_date']['end_date']) ?>（<?php echo $order['rate'] ? '工作日' : '每天' ?>配送）</li>
						<li>联系电话：<?php echo $order['phone'] ?></li>
					<?php } ?>
					<li>机器编号：<?php echo $order['vms']->vm_name.'('.$order['vms']->vmid.')' ?></li>
					<li>机器地址：<?php echo $order['vms']->address ?></li>
				</ul>
			</div>
		</div>
	<?php } ?>

	<?php if($count > PAGENUMBER){ ?>
		<div class="loadmore">
			<p>上拉加载更多</p>
		</div>
	<?php } ?>
<?php } ?>

<script src="http://apps.bdimg.com/libs/zepto/1.1.4/zepto.min.js"></script>
<script src="/sources/scripts/ui.js" ></script>
<script type="text/javascript">
$(function(){
	$('.loadmore').loadMoreRecords('<?php echo PAGENUMBER ?>', '.myorders');
});
</script>
</body>
</html>