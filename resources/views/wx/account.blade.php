<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
	<meta name="format-detection" content="telephone=no"/>
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta http-equiv="pragma" content="no-cache" />
	<title>购物车</title>
	<link href="<?php echo $cdn_url ?>/style/base.css?v=<?php echo $css_version ?>" type="text/css" rel="stylesheet" />
</head>
<body class="gray">
<header>
	<h1><img src="<?php echo $cdn_url ?>/images/common/logo.png" /></h1>
	<p>
		<a href="/wx/account"><span id="cartProductsAccount"></span></a>
		<a href="/wx/orders">我的订单</a>
	</p>
</header>
<div class="order space_top">
	<h1>我预定的</h1>
	<section class="box" >
		<?php /*
		<div class="product">
			<section>
				<!--
				 358 * 278
				-->
				<span><img src="<?php echo $cdn_url ?>/images/products/100016_l.jpg" /></span>
			</section>
			<h1>加钙鲜牛奶<span>(350ml)</span></h1>
			<p>￥3.50</p>
			<h3 class="changeCount">每日配送:
				<span>
					<button type="button"  class="grey"></button> 
					<input type="text" value="4" /> 
					<button type="button" class="grey"></button>
				</span>
			</h3>
		</div>
		*/ ?>
		<div id="myCartList">
			<div class="product">
				<section>
					<span>
						<img src="<?php echo $cdn_url ?>/images/products/100017_l.jpg" />
					</span>
				</section>
				<h1>加钙鲜牛奶<span>(200g)</span></h1>
				<p>￥3.50</p>
				<h3 class="changeCount">每日配送:
					<span>
						<button type="button"></button> 
						<input type="text" value="4" /> 
						<button type="button"></button>
					</span>
				</h3>
			</div>
		</div>
		<div class="addMore">
			<a href="/wx/list/<?php echo $vminfor['vmid'] ?>">再添加几瓶</a>
		</div>
	</section>
</div>

<div class="order">
	<h1>配送时间</h1>
	<section class="box date" id="deliverWeek">
		<dl>
			<dt>每日配送 <span></span> </dt>
			<dd>周一至周日<span>配送日当天 6:00 以后取</span> </dd>
		</dl>
		<dl class="on">
			<dt>工作日配送 <span></span></dt>
			<dd>周一至周五<span>配送日当天 6:00 以后取</span> </dd>
		</dl>
	</section>
</div>

<div class="order">
	<h1>配送地址</h1>
	<section class="box">
		<div class="address">
			<section>
				<p class="pos">
					<?php echo $vminfor['node_name'] ?>
				</p>
				<p class="building">
					<?php echo $vminfor['address'] ?>
				</p>
				<p class="phone">
					<input type="number" placeholder="我的手机号码(必填)" value="" id="userPhone" /> 
					<button style="display:none;"><span></span></button>
				</p>
			</section>
		</div>
	</section>
	<div class="msg" id="msg"></div>
</div>

<div class="order">
	<h1>预定周期</h1>
	<section class="box period" id="deliverDays">
		<span class="on">30 天</span>
		<span >60 天</span>
		<span>90 天</span>
	</section>
</div>

<div class="order last">
	<h1>优惠卡券</h1>
	<section class="box">
		<ul id="cardList">
			<li>50元现金券（满200元以上可用）</li>
			<li>50元现金券（满200元以上可用）</li>
			<li class="disable">50元现金券（满200元以上可用）</li>
		</ul>
	</section>
</div>
<div class="wxPay" style="display:;">
	<span>总共：<mark id="totalPrice">￥552.00</mark></span>
	<button>微信支付</button>
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

<script src="<?php echo $cdn_url ?>/scripts/lib/zepto.min.js"></script>
<script src="<?php echo $cdn_url ?>/scripts/ui.js?v=<?php echo $js_version ?>" ></script>
<script type="text/javascript">
	$('#cartProductsAccount').accountHandler();

	$('#cardList').radioBox('li:not(.disable)');
	$('#deliverDays').radioBox('span');
	$('#deliverWeek').radioBox('dl');

	$('#userPhone').checkMobilePhone();
	$('#myCartList').selectedHandler();
	$('#totalPrice').computerTotalPrice();

</script>
</body>
</html>