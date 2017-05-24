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
<?php 
	define("PAGENUMBER", 10); // 每页显示商品数量
?>
<header>
	<h1><img src="/sources/images/common/logo.png" /></h1>
	<p>
		<a href="/wx/history" class="h">历史订单</a>
	</p>
</header>
<?php 
	$user 	= $order['user'];
	$vms 	= $order['vms'];
	$products = $order['products'];
	$count 	= count($products);
?>
<div class="vminfor re">
	<h1>我的取货码：<mark><?php echo $order['user']->password ?></mark></h1>
	<p>在售货机的屏幕上输入此密码即可取货</p>
</div>

<div class="vminfor re o">
	<h1><?php echo $vms->vm_name ?><span>编号：<?php echo $vms->vmid ?></span></h1>
	<p><?php echo $vms->address ?></p>
</div>
<?php if( !$count ){ ?>
<div class="empty space">
	<p><img src="/sources/images/common/icon.png" /></p>
	<h3>暂无取货记录</h3>
	<!-- <a href="" class="blue_button">马上预定</a> -->
</div>
<?php } else { ?>
<?php foreach($products as $key => $value){ ?>
<div class="myorders <?php echo $key < PAGENUMBER ? 'show' : '' ?>">
	<div class="block a"><?php echo $value['date'] ?></div>
	<div class="block d">
		<?php foreach($value['details'] as $d){ ?>
		<section>
			<p><img src="<?php echo $d->img_url ?>" /></p>
			<h1><?php echo $d->product_name ?></h1>
			<h3><?php echo $d->volume.($d->unit === 1 ? 'ml' : 'g') ?>/瓶</h3>
			
			<?php 
				// 取货结果 200出货成功, 201待取货, 202出货确认中, 203用户放弃,释放该商品 400出货失败
				$orderStatusArr = array(
						'200' => '已取货',
						'201' => '待取货',
						'202' => '出货确认中',
						'203' => '过期未取',
						'400' => '出货失败'
					);
				$styleArr = array(
						'200' => 'x1',
						'201' => 'x3',
						'202' => 'x4',
						'203' => 'x5',
						'400' => 'x2'
					);
 				echo '<h4><span class="'.$styleArr[$d->order_status].'"></span>'.$orderStatusArr[$d->order_status].'</h4>';
 				if($d->order_status == '400'){
 					echo '<h5>出货失败，稍后系统会重新尝试出货</h5>';
 				}
			?>
			
		</section>
		<?php } ?>
	</div>
</div>
<?php } ?>

<?php if( $count > PAGENUMBER ){ ?>
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