<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
	<meta name="format-detection" content="telephone=no"/>
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta http-equiv="pragma" content="no-cache" />
	<title>商品详情</title>
	<link href="<?php echo $cdn_url ?>/style/base.css?v=<?php echo $css_version ?>" type="text/css" rel="stylesheet" />
</head>
<body class="grey">
<header>
	<h1><img src="<?php echo $cdn_url ?>/images/common/logo.png" /></h1>
	<p>
		<a href="/wx/account"><span id="totalCart"></span></a>
		<a href="/wx/orders">我的订单</a>
	</p>
</header>
<?php // if(){ ?>
	
<?php // }else{ ?>
	
<?php // } ?>
<div class="detail">
	<h3><img src="<?php echo $cdn_url ?>/images/products/default_d.jpg" data-src="<?php echo $detail->pic_t ?>" class="detailImgs" /></h3>
	<h1>
		<?php echo $detail->product_name ?>
		<?php if( isset($detail->volume) && !empty($detail->volume) ){ ?>
			<span>(<?php echo $detail->volume ?>)</span>
		<?php } ?>
		
		<mark>￥<?php echo round($detail->retail_price/100, 2) ?></mark>
	</h1>
</div>
<div class="container">
	<h2>商品详情</h2>
	<p><?php echo $detail->des ?></p>
	<section>
		<?php foreach($detail->detail_pics as $pic){ ?>
			<img src="<?php echo $cdn_url ?>/images/products/default_d.jpg" data-src="<?php echo $pic ?>" class="detailImgs" />
		<?php } ?>
	</section>
</div>
<footer>
	<p>目前共<mark id="totalSelected">0</mark>份</p>
	<section class="detailButtons" style="display:<?php echo $detail->count ? '' : 'none' ?>">
		<button class="blue_button" id="addToCart">加入购物车</button>
		<a href="/wx/account" class="green_button">去结算</a>
	</section>

	<section class="detailButtons" style="display:<?php echo $detail->count ? 'none' : '' ?>;">
		<!-- <span>已订完</span> --><a href="/wx/list/<?php echo $vmid ?>" class="green_button">继续购物</a>
	</section>
</footer>
<script src="<?php echo $cdn_url ?>/scripts/lib/zepto.min.js"></script>
<script src="<?php echo $host ?>sources/scripts/ui.js?v=<?php echo $js_version ?>" ></script>
<script type="text/javascript">
$(function(){
	$('body').detailHandler({
		pid: '<?php echo $detail->product_id ?>',
		pname: '<?php echo $detail->product_name ?>',
		oprice: '<?php echo $detail->original_price ?>',
		price: '<?php echo $detail->retail_price ?>',
		left: '<?php echo $detail->count ?>'
	});	
	$('.detailImgs').loadImages();
});

// 10cm * 10cm 
// 分辨率 300


</script>
</body>
</html>