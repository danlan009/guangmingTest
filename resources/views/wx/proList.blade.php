<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
	<meta name="format-detection" content="telephone=no"/>
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta http-equiv="pragma" content="no-cache" />
	<title>欢迎预定</title>
	<link href="<?php echo $cdn_url ?>/style/base.css?v=<?php echo $css_version ?>" type="text/css" rel="stylesheet" />
</head>
<body>
<?php 
	define("PAGENUMBER", 4); // 每页显示商品数量
?>
<header>
	<h1><img src="<?php echo $cdn_url ?>/images/common/logo.png" /></h1>
	<p>
		<a href="/wx/account/<?php echo $vmInfor['vmid'] ?>"><span id="cartProductsAccount" data-count=""></span></a>
		<a href="/wx/orders">我的订单</a>
	</p>
</header>
<div class="banner">
	<img src="<?php echo $cdn_url ?>/images/common/top.jpg" />
</div>
<div class="vminfor">
	<h1><?php echo $vmInfor['node_name'] ?><span>编号：<?php echo $vmInfor['vmid'] ?></span></h1>
	<p><?php echo $vmInfor['address'] ?></p>
	<a href="/wx/vmlist" class="blue_button">更换</a>
</div>
<section class="products_list" id="products_list">
	<?php 
		$productsInfo = array();
		foreach ($products as $key => $value) { 
	?>
		<div class="pro <?php echo $key < PAGENUMBER ? 'show' : '' ?>">
			<p>
				<a href="/wx/detail/<?php echo $vmInfor['vmid'].'/'.$value->product_id ?>">
					<img src="<?php echo $cdn_url ?>/images/products/default.jpg" data-src="<?php echo $value->pic_l ?>" class="pListImgs" />
					<mark><?php echo isset($value->volume) && !empty($value->volume) ? $value->volume : '' ?></mark>
					<?php if(isset($value->tag_name) && !empty($value->tag_name)){ ?>
						<span class="<?php echo $value->tag_name == '新品' ? '' : 'hot' ?>"></span>
					<?php } ?>
					<?php
						echo $value->count ? '' : '<em></em>';
					?>
				</a>
			</p>
			<h1><?php echo $value->product_name ?><span></span></h1>
			<h2>
				￥<?php echo round($value->retail_price/100, 2) ?>
				<?php if($value->retail_price != $value->original_price){ ?>
					<del>￥<?php echo round($value->original_price/100, 2) ?></del>
				<?php } ?>
				<button class="<?php echo 'p_'.$value->product_id.' '.($value->count ? 'blue_button' : 'grey_button') ?>" data-id="<?php echo $value->product_id ?>">预定</button>
			</h2>
		</div>
	<?php 
			$productsInfo[] = array(
					'product_id' 	=> $value->product_id,
					'product_name' 	=> $value->product_name,
					'volume' 		=> $value->volume,
					'original_price'=> $value->original_price,
					'retail_price' 	=> $value->retail_price,
					'count' 		=> $value->count
				);
		} 
	?>
</section>

<?php if(count($products) > PAGENUMBER){ ?>
	<div class="loadmore">
		<p>上拉加载更多</p>
	</div>
<?php } ?>

<script src="<?php echo $cdn_url ?>scripts/lib/zepto.min.js"></script>
<script type="text/javascript">
window.sessionStorage.setItem('productsListObj', (function(){
	var plist = '<?php echo json_encode($productsInfo) ?>',
		newList = {},
		item = null;
	plist = JSON.parse(plist);
	for(var i=0,len=plist.length; i<len; i++){
		item = plist[i];
		newList[item['product_id']] = {
			'pid': item['product_id'],
			'pname': item['product_name'],
			'volume': item['volume'],
			'oprice': item['original_price'],
			'rprice': item['retail_price'],
			'left': item['count']
		};
	}
	return JSON.stringify(newList);
})() );

if(!window.sessionStorage['selectedProducts']){
	window.sessionStorage['selectedProducts'] = JSON.stringify({
		"products": { },
		"originTotal": 0,	// 优惠前的总价
		"retailTotal": 0, 	// 优惠后的总价
	    "total": 0,			// 总数量
	    "vmid": "<?php echo $vmInfor['vmid'] ?>"
	});
}else{
	var selected = window.sessionStorage['selectedProducts'],
		selected = JSON.parse(selected);
	if(!selected || (selected && selected['vmid'] != "<?php echo $vmInfor['vmid'] ?>")){
		window.sessionStorage['selectedProducts'] = JSON.stringify({
			"products": { },
		    "total": 0,
		    "originTotal": 0,	// 优惠前的总价
			"retailTotal": 0, 	// 优惠后的总价
		    "vmid": "<?php echo $vmInfor['vmid'] ?>"
		});
	}
}

</script>
<script src="/sources/scripts/ui.js?v=<?php echo $js_version ?>" ></script>
<script type="text/javascript">
$(function(){
	$('#products_list button').addToCart($('#cartProductsAccount'));
	$('.show .pListImgs').loadImages();
	$('.loadmore').loadMoreRecords('<?php echo PAGENUMBER ?>', '.pro');
});
</script>
</body>
</html>