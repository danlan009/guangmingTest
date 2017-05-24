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
	<link rel="stylesheet" href="/sources/style/bootstrap.min.css">
	<style type="text/css">
		.modal-content{
			width: 60%;
			height: 30%;
			
			position: absolute;
			text-align: center;
			left:0;
			right:0;
			top: 0;
			bottom: 0;
			margin: auto;
		}
		
	</style>
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
						<span style="position:relative">
							<a href="{{$toUrl}}?vmid={{$vm['vmid']}}" data="{{$vm['vmid']}}" class='list-group-item' style="margin-right:60px">
								<h4 class="list-group-item-heading">{{ $vm['vmid'].'-'.$vm['vm_name']}}</h4>
								<!-- <p class="list-group-item-text">{{ $vm['vm_name'] }}</p> -->
							</a>
							<button type="button" name="btn_clear" class="btn btn-primary" style="position:relative;float:right;top:-40px">清货</button>
						</span>

					@endforeach
				</div> 
			
			@endforeach
	  </ul>

		<div id="myModal" class="modal" tabindex="-1" role="dialog" style="">
		  <div class=" modal-sm" role="document" style="width:350px; ">
		    <div class="modal-content" style="">
		      		<center><h4>提示</h4></center><center><h3>确定要清货么?</h3></center>
		    		<button id='btn_cancel' type="button" class="btn btn-default" style="width:50%;float:left;position:absolute;bottom:0px;left:0px">取消</button>
		      		<button id='btn_confirm' type="button" class="btn btn-default" style="width:50%;float:right;position:absolute;bottom:0px;right:0px" >确认</button>
		    </div>
		  </div>
		</div>

	  </div>
	</div>
</div>
<script src="/sources/scripts/lib/zepto.min.js"></script>
<script>

$(function(){
	// console.log('222');
	var _this_vmid = 0;
	$("button[name='btn_clear']").on('click',function(){
		$('#myModal').show();
		_this_vmid = $(this).prev().attr('data');
		console.log(_this_vmid);
		
	});
	$('#btn_confirm').on('click',function(){
		$.ajax({ 
			type : 'GET',
			url : 'ajax_clear',
			data : {
				vmid:_this_vmid
			},
			timeout : 3000,
			success : function(){
				alert('售货机:'+_this_vmid+'清货成功');
				$('#myModal').hide();
			},
			error : function(){
				alert('清货失败');
				$('#myModal').hide();
			}
		});
	});

	$('#btn_cancel').on('click',function(){
		$('#myModal').hide();
	});
});
</script>
</body>
</html>