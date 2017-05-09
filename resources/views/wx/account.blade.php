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
		<div id="myCartList">
			<?php /*
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
			</div>*/
			?>
		</div>
		<div class="addMore">
			<a href="/wx/list/<?php echo $vminfor['vmid'] ?>">再添加几瓶</a>
		</div>
	</section>
</div>

<div class="order">
	<h1>配送时间</h1>
	<section class="box date" id="deliverWeek">
		<dl data-id="0">
			<dt>每日配送 <span></span> </dt>
			<dd>周一至周日<span>配送日当天 6:00 以后取</span> </dd>
		</dl>
		<dl data-id="1" class="on">
			<dt>工作日配送 <span></span></dt>
			<dd>周一至周五<span>配送日当天 6:00 以后取</span> </dd>
		</dl>
	</section>
</div>

<div class="order">
	<h1>配送地址</h1>
	<section class="box deliver">
		<p class="pos"><?php echo $vminfor['node_name'] ?></p>
		<p class="building"><?php echo $vminfor['address'] ?></p>
		<p class="phone">
			<input type="text" placeholder="我的手机号码(必填)" value="" id="userPhone" /> 
			<!-- <button style="display:none;"><span></span></button> -->
		</p>
	</section>
	<div class="msg" id="msg"></div>
</div>

<div class="order">
	<h1>预定周期</h1>
	<section class="box period" id="deliverDays">
		<span data-num="30" class="on">30 天</span>
		<span data-num="60" >60 天</span>
		<span data-num="90" >90 天</span>
	</section>
</div>

<div class="order last">
	<h1>优惠卡券</h1>
	<section class="box">
		<ul id="cardList">
			<li data-card-id="" data-card-name="">50元现金券（满200元以上可用）</li>
			<li data-card-id="" data-card-name="">50元现金券（满200元以上可用）</li>
			<li data-card-id="" data-card-name="" class="disable">50元现金券（满200元以上可用）</li>
		</ul>
	</section>
</div>
<div class="wxPay" style="display:;">
	<span>总共：<mark id="totalPrice">￥552.00</mark></span>
	<button id="btnWxPay" type="button">微信支付</button>
</div>

<div class="wxPay waiting" style="display:none;">
	<span>需支付
		<mark>
			<em><b></b></em>
			<em><b></b></em>
			<em><b></b></em>
		</mark>
	</span>
	<form>
		<input />
		<button>微信支付</button>
	</form>

	<?php 
	// 'intention':[
	// 	'vmid'=>'0081801',
	// 	'channel'=>'1',
	//     'total_price'=>'100',
	//     'retail_price'=>'100',
	//     'products'=>array(
	// 	    array(
	// 		    	'product_id'=>1,
	// 		    	'product_name'='鲜奶',
	// 		    	'original_price'=>100,
	// 		    	'retail_price'=>100,
	// 		    	'num'=>2
	// 	    	),
	// 	    array(
	// 		    	'product_id'=>2,
	// 		    	'product_name'='酸奶',
	// 		    	'original_price'=>100,
	// 		    	'retail_price'=>100,
	// 		    	'num'=>2
	// 	    	),
	//     ),
	//     'card_id'='1111',
	//     'card_name'='满10减5元',
	//     'type'=>30,
	//     'rate'=>0,
	//     'phone'='15612345678'
 //    ],
     
	?>
	
</div>

<script src="<?php echo $cdn_url ?>/scripts/lib/zepto.min.js"></script>
<script type="text/javascript">
	var collectionsObj = {
		'card_id': '',		// 卡券id
		'card_name': '',	// 卡券名称
		'type': '30',		// 预定周期
		'rate': '0',		// 0 每天配送, 1工作日配送
		'phone': '',		// 用户电话
		'vmid': '<?php echo $vminfor["vmid"] ?>'
	};
</script>
<script src="<?php echo $host ?>sources/scripts/ui.js?v=<?php echo $js_version ?>" ></script>
<script type="text/javascript">
	$('#cartProductsAccount').accountHandler();
	$('#cardList').radioBox('li:not(.disable)', 'card');
	$('#deliverDays').radioBox('span', 'days');
	$('#deliverWeek').radioBox('dl', 'week');
	$('#userPhone').checkMobilePhone();
	$('#myCartList').selectedHandler();
	$('#totalPrice').computerTotalPrice();
	$('.changeCount').selectedCountHandler();
	$('#btnWxPay').weixinPay();
</script>
</body>
</html>