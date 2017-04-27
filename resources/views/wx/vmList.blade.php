<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
	<meta name="format-detection" content="telephone=no"/>
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta http-equiv="pragma" content="no-cache" />
	<title>选择点位</title>
	<link href="/style/base.css" type="text/css" rel="stylesheet" />
</head>
<body class="grey">
<div class="searchBox">
	<p>
		<button id="btnSearchVms"></button>
		<input type="text" placeholder="输入点位名称" /> 
		<span id="btnClearText" style="display:none;"></span>
	</p>
</div>
<section class="vmlist" id="vmList">
	<?php 
		$count = count($vms);
		$index = 0;
		echo "<div>";
		foreach ($vms as $v) { 
			$index++; 
			if($index == floor($count/2)){
				echo "</div><div>";
			}

			$vms = $v['vms'];
			$vmsCount = count($vms);
	?>

	<p>
		<a href="" class="<?php echo $vmsCount>1 ? 'multi' : '' ?>"><?php echo $v['node_name'] ?></a>
		<?php 
			if($vmsCount > 1){
				echo '<span>';
				foreach ($vms as $key => $value) {
					echo '<a href="/wx/list/'.$value['vmid'].'" data-name="'.$v['node_name'].$value['vm_name'].'" data-vmid="'.$value['vmid'].'">'.$value['vm_name'].'</a>';
				}
				echo '</span>';
			}
		?>
	</p>

	<?php 
		}
		echo "</div>";
	?>
</section>
<section class="search_result" id="search_result"></section>
<script src="http://apps.bdimg.com/libs/zepto/1.1.4/zepto.min.js"></script>
<script src="/scripts/ui.js" ></script>
<script type="text/javascript">
$(function(){
	var vmlist = $('#vmList'),
		btnSearch = $('#btnSearchVms'),
		inputBox = btnSearch.next(),
		links 	= vmlist.find('span>a');
	vmlist.listHandler();
	btnSearch.searchVms(links);

});
</script>
</body>
</html>