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
		<a href="/wx/account/<?php echo $vminfor["vmid"] ?>"><span id="cartProductsAccount"></span></a>
		<a href="/wx/orders">我的订单</a>
	</p>
</header>
<div class="order space_top">
	<h1>我预定的</h1>
	<section class="box" >
		<div id="myCartList"></div>
		<div class="addMore">
			<a href="/wx/list/<?php echo $vminfor['vmid'] ?>">再添加几瓶</a>
		</div>
	</section>
</div>

<div class="order">
	<h1>配送时间</h1>
	<section class="box date" id="deliverWeek">
		<dl data-id="0" class="on">
			<dt>每日配送 <span></span> </dt>
			<dd>周一至周日<span>配送日当天 6:00 以后取</span> </dd>
		</dl>
		<dl data-id="1">
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
			<input type="text" placeholder="我的手机号码(必填)" value="<?php echo $phone ? $phone : '' ?>" id="userPhone" /> 
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
			<li data-card-id="" data-card-name="" data-card-least="0" data-card-reduce="0" class="on" >暂不使用卡券</li>
			<?php 
				if($cardList){
					foreach ($cardList as $card) {
						$cardDetail = $card['detail'];
						$cardType = $cardDetail['card_type'];
						// 代金券且可以核销的
						if($cardType == 'CASH'){
							$baseInfor = $cardDetail['cash']['base_info'];
							// 判断卡券是否可用
							echo '<li data-card-id="'.$baseInfor['id'].'" data-card-code="'.$card['code'].'" data-card-name="'.$baseInfor['title'].'" ';
							echo 'data-card-least="'.$cardDetail['cash']['least_cost'].'" data-card-reduce="'.$cardDetail['cash']['reduce_cost'].'" ';
							echo '>'.$baseInfor['title'];
							echo '</li>';
						}
					} 
				}
			?>
			<?php /*
			<li data-card-id="A1" data-card-name="A1券" data-card-least="500" data-card-reduce="400" >4元代金券（满5元以上可用）</li>
			<li data-card-id="A2" data-card-name="A2券" data-card-least="300" data-card-reduce="100"  >1元现金券（满3元以上可用）</li>
			<li data-card-id="A2" data-card-name="A2券" data-card-least="1000" data-card-reduce="800" class="disable" >8元代金券（满10元以上可用）</li>
			*/ ?>
		</ul>
	</section>
</div>
<div class="wxPay" style="display:;">
	<span>总共：<mark id="totalPrice">￥0</mark></span>
	<button id="btnWxPay" type="button">微信支付</button>
</div>
<?php /*
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
</div>
*/ ?>

<div class="mask" id="mask" style="display:none;">
	<section>
		<p class="waiting"></p>
		<h3>支付中，请稍后</h3>
	</section>
</div>

<script src="<?php echo $cdn_url ?>scripts/lib/zepto.min.js"></script>
<script type="text/javascript">
	var collectionsObj = {
		'card_id': '',		// 卡券id
		'card_name': '',	// 卡券名称
		'card_code': '',
		'type': '30',		// 预定周期
		'rate': '0',		// 0 每天配送, 1工作日配送
		'phone': '<?php echo $phone ? $phone : "" ?>',		// 用户电话
		'vmid': '<?php echo $vminfor["vmid"] ?>',
		'reduce': 0
	};
</script>
<script type="text/javascript" src="http://res.wx.qq.com/open/js/jweixin-1.2.0.js"></script>
<script src="/sources/scripts/ui.js?v=<?php echo $js_version ?>" ></script>
<script type="text/javascript">
	$('#cartProductsAccount').accountHandler();
	$('#cardList').radioBox('li', 'card');
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