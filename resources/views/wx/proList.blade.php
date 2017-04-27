<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
	<meta name="format-detection" content="telephone=no"/>
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta http-equiv="pragma" content="no-cache" />
	<title>欢迎预定</title>
	<link href="/style/base.css" type="text/css" rel="stylesheet" />
</head>
<body>
<header>
	<h1><img src="/images/common/logo.png" /></h1>
	<p>
		<a href="/wx/account"><span>12</span></a>
		<a href="/wx/orders">我的订单</a>
	</p>
</header>
<div class="banner">
	<img src="/images/common/top.jpg" />
</div>
<div class="vminfor">
	<h1><?php echo $vmInfor['vm_name'] ?><span>编号：<?php echo $vmInfor['vmid'] ?></span></h1>
	<p><?php echo $vmInfor['address'] ?></p>
	<a href="/wx/vmlist" class="blue_button">更换</a>
</div>
<section class="products_list">
	<?php foreach ($products as $key => $value) { ?>
		<div class="pro">
			<p>
				<a href="/wx/detail/<?php echo $vmInfor['vmid'].'/'.$value->product_id ?>">
					<img src="/images/products/default.jpg" data-src="<?php echo $value->pic_url ?>" />
				</a>
			</p>
			<h1><?php echo $value->product_name ?><span><?php echo isset($value->volume) && !empty($value->volume) ?></span></h1>
			<h2>
				￥<?php echo round($value->retail_price/100, 2) ?>
				<del>￥<?php echo round($value->original_price/100, 2) ?></del>
				<button class="blue_button">预定</button>
			</h2>
		</div>
	<?php } ?>
</section>

<div class="loadmore">
	<p>上拉加载更多</p>
</div>

<script src="http://apps.bdimg.com/libs/zepto/1.1.4/zepto.min.js"></script>
<script src="/scripts/ui.js" ></script>
</body>
</html>