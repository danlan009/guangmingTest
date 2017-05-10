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
		<a href="">我的订单</a>
	</p>
</header>
<div class="vminfor re">
	<h1><?php echo $vmInfor['node_name'] ?><span>编号：<?php echo $vmInfor['vmid'] ?></span></h1>
	<p><?php echo $vmInfor['address'] ?></p>
</div>
<div class="order space_top2">
	<section class="box">
		<h4>从<mark>1</mark>天后<mark>5月5日周六</mark>,每天配送<mark>5</mark>瓶</h4>
		<div class="product">
			<section>
				<span><img src="/sources/images/products/100016_l.jpg" /></span>
			</section>
			<h1>加钙鲜牛奶<span>(350ml)</span></h1>
			<h2><mark>3</mark>瓶</h2>
		</div>
		<div class="product">
			<section>
				<span>
					<img src="/sources/images/products/100017_l.jpg" />
				</span>
			</section>
			<h1>加钙鲜牛奶<span>(200g)</span></h1>
			<h2><mark>2</mark>瓶</h2>
		</div>
	</section>
</div>

<div class="order">
	<section class="box deliver">
		<p class="day">共配送<mark>30</mark>天</p>
		<p class="phone">您的联系电话：13800138000</p>
		<p class="total">总计：<mark>323.50</mark>元</p>
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
</body>
</html>