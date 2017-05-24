<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
	<meta name="format-detection" content="telephone=no"/>
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta http-equiv="pragma" content="no-cache" />
	<title>预定完成</title>
	<link href="/sources/style/base.css" type="text/css" rel="stylesheet" />
</head>
<body class="gray">
<header>
	<h1><img src="/sources/images/common/logo.png" /></h1>
	<p>
		<a href="" class="disable"><span>12</span></a>
		<a href="/wx/orders">我的订单</a>
	</p>
</header>
<div class="vminfor re">
	<h1><?php echo $vmInfor['node_name'] ?><span>编号：<?php echo $vmInfor['vmid'] ?></span></h1>
	<p><?php echo $vmInfor['address'] ?></p>
</div>
<div class="order space_top2">
	<section class="box">
		<?php 
			$weekArr = ['周日', '周一', '周二', '周三', '周四', '周五', '周六'];
		?>
		<h4>从<mark><?php echo date('m月d日').$weekArr[date('w')] ?></mark>，每次配送<mark><?php echo $order['count'] ?></mark>瓶</h4>
		<?php foreach($order['products'] as $p){ ?>
			<div class="product">
				<section>
					<span><img src="<?php echo $p['pic']  ?>" /></span>
				</section>
				<h1>
				<?php 
					echo $p['pname'];
					if($p['volume']){
						echo '<span>('.$p['volume'].')</span>';
					}
				?>
				</h1>
				<h2><mark><?php echo $p['num'] ?></mark>瓶</h2>
			</div>
		<?php } ?>
	</section>
</div>

<div class="order">
	<section class="box deliver">
		<p class="day">
			共配送<mark><?php echo $order['type']  ?></mark>天
			（<mark><?php echo $order['rate'] ? '工作日' : '每天' ?>配送</mark>）
		</p>
		<p class="phone">您的联系电话：<?php echo $order['phone'] ?></p>
		<p class="total">总计：<mark><?php echo round($order['retail_price'] / 100, 2) ?></mark>元</p>
	</section>
</div>

<div class="wxPay waiting" style="display:none;">
	<span>需支付
		<mark>
			<em><b></b></em>
			<em><b></b></em>
			<em><b></b></em>
		</mark>
	</span>
	<button>微信支付</button>
</div>

<div class="result">
	<h1>预定完毕</h1>
</div>

<script src="http://apps.bdimg.com/libs/zepto/1.1.4/zepto.min.js"></script>
<script src="/sources/scripts/ui.js" ></script>
<script type="text/javascript">
$('.disable').on('click', function(e){
	e.preventDefault();
});
</script>
</body>
</html>