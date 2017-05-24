<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
	<meta name="format-detection" content="telephone=no"/>
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta http-equiv="pragma" content="no-cache" />
	<title>选择点位</title>
	<link href="<?php echo $cdn_url ?>/style/base.css?v=<?php echo $css_version ?>" type="text/css" rel="stylesheet" />
</head>
<body class="grey">
<div class="searchBox">
	<form>
		<button id="btnSearchVms" type="button"></button>
		<input type="text" placeholder="输入点位名称" /> 
		<span id="btnClearText" style="display:none;"></span>
	</form>
</div>
<section class="vmlist" id="vmList">
	<?php 
		$count = count($vms);
		$index = 0;
		echo "<div>";
		foreach ($vms as $v) { 
			$index++; 
			

			$vms = $v['vms'];
			$vmsCount = count($vms);
	?>

	<p>
		<a href="" class="<?php echo $vmsCount>1 ? 'multi' : '' ?>"><?php echo $v['node_name'] ?></a>
		<?php 
			if($vmsCount > 1){
				echo '<span>';
				foreach ($vms as $key => $value) {
					echo '<a href="/wx/list/'.$value['vmid'].'?c='.$channel.'" data-name="'.$v['node_name'].$value['vm_name'].'" data-vmid="'.$value['vmid'].'">'.$value['vm_name'].'</a>';
				}
				echo '</span>';
			}
		?>
	</p>

	<?php 	
			if($count % 2 == 0){
				echo "</div><div>";
			}
		}
		echo "</div>";
	?>
</section>
<section class="search_result" id="search_result"></section>
<script src="<?php echo $cdn_url ?>/scripts/lib/zepto.min.js"></script>
<script src="/sources/scripts/ui.js?v=<?php echo $js_version ?>" ></script>
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