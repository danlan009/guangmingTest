<!DOCTYPE html>
<html lang="zh-cn">
<head> 
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
	<title>补货</title>
	<link rel="stylesheet" href="/sources/style/bootstrap.min.css">
	
</head>
<body>  

<div id="mainDiv" class="container"> 
	<div class="panel panel-default">
		  <div class="panel-heading"> 
		    <!-- <h3 class="panel-title">选择要补货的柜子</h3> -->
		  </div>
		  <div class="panel-body container"> 
		  		<div class='row' style='padding:10px'>

		  			<div id="show_sku" class="jumbotron" style="padding:10px;margin-bottom:0px">
		  			  <h1 id="sku_seq">货道 <label>1</label></h1>
		  			  <h3 id="p_name">商品:大果块黄桃+芒果</h3>
		  			  <h3 id="normal">原有: <label>5</label></h3>
		  			  <h3 id="warn">过期: <label>0</label></h3>
		  			  <h3 id="default_add">增加: <label>0</label> </h3>
		  			  <input type="hidden" id="actual_add" value="">
		  			  <p><button id="btn_alter" class="btn btn-default" href="#" type="button">更正</button></p>
		  			</div>
					<a id="btn_pre" class='col-xs-3 btn btn-primary btn-lg btn_cubes' >上个货道</a>
					<div class='col-xs-1'>&nbsp;</div>
					<a id="btn_next" class='col-xs-3 btn btn-primary btn-lg btn_cubes'>下个货道</a>
		  		</div>
				
		  </div>
	</div>

	<!-- Modal -->
	<div class="modal" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content" style="width:90%;margin:0 auto">
	      <div class="modal-header">
	        <h4 class="modal-title" id="myModalLabel">补货列表</h4>
	      </div>
	      <div class="modal-body">
	         <span>
	         	货道 1: 
	         	| <span class="label label-default">原有:</span> 3 
	         	| <span class="label label-success">增加:</span> 4
	         </span>
	      </div>
	      <div class="modal-footer">
	        <button type="button" id="btn_dia_close" class="btn btn-default" data-dismiss="modal">修改</button>
	        <button type="button" id="btn_dia_save" class="btn btn-primary">保存</button>
	      </div>
	    </div>
	  </div>
	</div>

	<div class="panel panel-default">
  		<div class="panel-body text-center">
    		若不继续补货，请点击<br/>
    		<button id="btn_done" type="button" class="btn btn-warning" data-toggle="modal">补货完成</button>
  		</div>
	</div>	
	<div id="div_finish" class="text-center">
  		<span class="glyphicon glyphicon-trash" aria-hidden="true"></span>&nbsp;
  		<a href=""></a>
	</div>	

</div>
<!-- <script src="https://ufan.ubox.cn/js/jquery.min.1.11.2.js?2015081102"></script> -->
<!-- <script src="/scripts/bootstrap.min.js?2015081102"></script> -->
<script src="/sources/scripts/lib/zepto.min.js"></script>
<script src="/sources/scripts/lib/zTouch.js" type="text/javascript"></script>
<script>


$.fn.setData = function(seq,obj){
	var _this = $(this);
	if(!obj){
		alert('error');
	}else{
		_this.find('#sku_seq label').html(seq);
		_this.find('#p_name').html('商品: '+ obj.product_name);
		_this.find('#normal label').html(obj.normal);
		_this.find('#warn label').html(obj.warn);
		_this.find('#default_add label').html( (obj.actual_add)?obj.actual_add:obj.default_add );
	}
}

// 给文本(p标签内)添加输入框
$.fn.alterToWritable = function(element){ 
	var _this = $(this);
	var label = _this.find('label');
	var num = label.text();
	_this.find('label').replaceWith('<input type="text" value="'+num+'" size="5">');
}

// 取消输入框,变回文本
$.fn.alterToText = function(){ 
	var _this = $(this);
	var num = _this.find('input').val();
	_this.find('input').replaceWith('<label>'+num+'</label>');
}

$.fn.checkNum = function(num,sku_obj){
	// 判断数量格式是否合法
	var num = parseInt(num);
	var total = num + sku_obj.normal;
	if(isNaN(num) || total>sku_obj.sku_size){ 
		$('#default_add input').trigger('focus').css('border','1px solid red');
		return false;
	}else{
		return true;
	}
}

$(function(){
	// 验证该售货机是否配置货道,没有配置则跳回start_supplyment
	var nv = $("#hello");
	var vmid = "{{ $vmid }}";
	
	var data = <?php echo $supplyData ?>;
	
	var arr = Object.keys(data);
	var min_index = arr[0]; // 获取货道最小序列号
	var index = min_index;

	$('#btn_pre').attr('disabled',true);
	// console.log(min_index);
	var max_index = arr[arr.length-1]; // 获取货道最大序列号
	var current = new Object();

	//初始化第一页(第一条货道信息)
	current = data[index];
	$('#show_sku').setData(index,current);

	$('#btn_pre').on('click',function(){
		//判断'增加'数据是否合法
		if($('#default_add').find('input').length){
			$('#default_add input').trigger('focus').css('border','1px solid red');
			return false;
		}
		// 防止越界
		if(index == min_index){
			return false; // 阻止点击事件
		}
		var cur_index = --index;
		for (var i = cur_index; i >= min_index; i--) {
			if(i == min_index){
				$('#btn_pre').attr('disabled',true);
				
			}
			if(data[i]){
				current = data[i];
				index = i;
				$('#show_sku').setData(index,current);
				$('#btn_next').removeAttr('disabled');
				break;
			}
		};
	});

	$('#btn_next').on('click',function(){
		//判断'增加'数据是否合法
		if($('#default_add').find('input').length){
			$('#default_add input').trigger('focus').css('border','1px solid red');
			return false;
		}

		// 防止越界
		if(index == max_index){
			return false; // 阻止点击事件
		}

		var cur_index = ++index;
		for (var i = cur_index; i <= max_index; i++) {
			if(i == max_index){
				$('#btn_next').attr('disabled',true);
			}
			if(data[i]){
				current = data[i];
				index = i;
				$('#show_sku').setData(index,current);
				$('#btn_pre').removeAttr('disabled');
				break;
			}
		};
		
		// console.log(current);
	});

	$('#btn_alter').on('click',function(){
		container = $('#default_add');
		if(container.find('input').length){
			var seq = $('#sku_seq label').text();
			var num = container.find('input').val(); //填写的数量
			sku_obj = data[seq]; // 取出原始货道补货数据
			if(container.find('input').checkNum(num,sku_obj)){
				container.alterToText();
				// 填写的数量存入data[seq]
				if(!(data[seq].actual_add = num)){
					return false;
				}
				$(this).text('更正');
				
			}
		}else{
			container.alterToWritable();
			$(this).text('确定');
		}

	});


	$('#btn_done').on('click',function(){
		$('#myModal').show();
		var addHtml = '';
		$.each(data,function(index,item){
			// console.log(data[index]);
			addHtml += '<h5>货道 '+index+': 　| 　<span class="label label-default">原有:</span> '+item.normal+' 　|　 <span class="label label-success">增加:</span> '+((item.actual_add)?item.actual_add:item.default_add)+'</h5>';
			// 未更改数量的货道,默认增加量作为实际增加量(手动修改为0被记录为'0')
			data[index].actual_add = (item.actual_add?item.actual_add:item.default_add);
		});
		$('#myModal .modal-body span').html(addHtml);
	});

	$('#btn_dia_close').on('click',function(){
		$('#myModal').hide();
	});

	$('#btn_dia_save').on('click',function(){
		// 计算是否有新补货
		var total = 0;
		$.each(data,function(index,item){
			total += item.actual_add;
		});
		if(!total){
			alert('没有补货!');
			window.location.href="start_supplyment";	
		}else{
			$.ajax({
				type : 'POST',
				url : 'ajax_receive_data',
				data : {
							data:JSON.stringify(data),
							vmid:vmid
						},
				timeout : 3000,
				dataType : 'json',
				success : function(d){
					alert('售货机:'+vmid+'补货成功!');
					window.location.href="start_supplyment";
				},
				error : function(){
					console.log('ajax error!');
				}
			});
		}
		
	});
	
});

</script>
</body>
</html>