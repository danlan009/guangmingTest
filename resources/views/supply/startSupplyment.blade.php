<!DOCTYPE html>
<?php 
$toUrl = 'list_cubes';
$title = '开始补货';  
if($mode==1){ 
	$toUrl = 'list_skus'; 
	$title = '补货';
}
$arr = array();
$group = array();
// foreach($nodes as $v){
// 	$arr['inner_code'] = $v->inner_code;
// 	$arr['node_name'] = $v->node_name;
// 	$group[] = $arr;
// }
?>
<html lang="zh-cn">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
	<title><?php echo $title;?></title>
	<link rel="stylesheet" href="/style/bootstrap.min.css">
</head>
<body>
<div id="mainDiv" class="container">
	<div class="panel panel-default">
	  <div class="panel-heading">
	    <h3 class="panel-title">请点选售货机编号</h3>
	  </div> 
	  <div class="panel-body">
		  
	  <ul class="list-group">
	  	   
			@foreach($nodes as $node)
				<div class="">
					<h4 class="">
						{{ $node['id'].'　'.$node['node_name'] }}
					</h4>
					
					@foreach($node['vms'] as $vm)
						<a href="{{$toUrl}}?vmid={{$vm['vmid']}}" class='list-group-item'>
							<h4 class="list-group-item-heading">{{ $vm['vmid'] }}</h4>
							<p class="list-group-item-text">{{ $vm['vm_name'] }}</p>
						</a>
					@endforeach
				</div>
			
			@endforeach
	  </ul>
	  </div>
	</div>
</div>
<script src="/scripts/lib/zepto.min.js"></script>
<script src="/scripts/bootstrap.min.js"></script>
<script>
$(function(){
	// var nodes = <?php echo json_encode($nodes);?>;
	// $(document).on('keyup','#groupSearch',function(){
	// 	var value = $(this).val();
	// 	var groups = nodes;
	// 	var content = '';
	// 	$.each(groups,function(i,n){
	// 		if(n.node_name.indexOf(value) !=-1){
	// 			content +='<a href="<?php echo $toUrl;?>?ic='+ (n.inner_code === null ? '' : n.inner_code)+'&group='+ n.group_id+'" class="list-group-item">'+
	// 				'<h4 class="list-group-item-heading">'+
	// 				n.inner_code+
	// 				'</h4><p class="list-group-item-text">'+
	// 				n.node_name+
	// 				'</p></a>';
	// 		}
	// 	});
	// 	$('.list-group').html(content);
	// });
});
</script>
</body>
</html>