<!DOCTYPE html>
<html lang="zh-cn">
<head> 
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1,minimum-scale=1,user-scalable=no">
	<title>补货</title>
	<link rel="stylesheet" href="/sources/style/bootstrap.min.css">
	
	<style type="text/css">
		#touchBox{width:100%;cursor:default;cursor: move;overflow:hidden;margin-bottom:10px;}
		#innerBox{width:1000%;margin:0px;padding:0px;position:relative;overflow:hidden}
		#innerBox li{position:relative;float:left;width:20%;list-style:none;text-align:left;}

		#btn_prev{border-top-left-radius: 20px;border-bottom-left-radius: 20px;padding-left:15px;}
		#btn_next{border-top-right-radius: 20px;border-bottom-right-radius: 20px;padding-right:15px;}
	</style>
</head>
<body>  

<div id="mainDiv" class=""> 
	<div class=""> 
		  <div class="panel-heading"> 
		    <!-- <h3 class="panel-title">选择要补货的柜子</h3> -->
		  </div>
		  <div class="panel-body"> 
		  		<div id="touchBox" class="">
			  		<ul id="innerBox">
			  			
			  		</ul>
		  		</div>
			  	<button name="btn_alter" class="btn btn-default" href="#" type="button">更正</button>

		  		<center><button id="btn_prev" class="btn btn-primary" style="margin-right:50px">上一张</button> <button id="btn_next" class="btn btn-primary">下一张</button></center>
				
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
$.fn.alterToWritable = function(){ 
	var _this = $(this);
	console.log(_this);
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
	var _this = $(this);
	// 判断数量格式是否合法
	var num = parseInt(num);
	var total = num + sku_obj.normal;
	if(isNaN(num) || total>sku_obj.sku_size){ 
		_this.trigger('focus').css('border','1px solid red');
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
	var length = arr.length;

	var min_index = arr[0]; // 获取货道最小序列号
	var index = min_index;

	var skusHtml = '';
	$.each(data,function(index, sku) {
		skusHtml += '<li>'
						+'<div id="'+'sku_'+index+'_show'+'" class="jumbotron" style="padding:10px;margin-bottom:0px">'
						+  '<h1 name="sku_seq">货道 <label>'+index+'</label></h1>'
						+  '<h3 name="p_name">商品:'+sku.product_name+'</h3>'
						+  '<h3 name="normal">原有: <label>'+sku.normal+'</label></h3>'
						+  '<h3 name="warn">过期: <label>'+sku.warn+'</label></h3>'
						+  '<h3 name="default_add">增加: <label>'+sku.default_add+'</label> </h3>'
						+  '<input type="hidden" name="actual_add" value="">'
						// +  '<button name="btn_alter" class="btn btn-default" href="#" type="button">更正</button>'
						+'</div>'
					+'</li>';
					// +'<button name="btn_alter" class="btn btn-default" href="#" type="button">更正</button>';
	});

	var num1 = length.toString()+'00%';
	var num2 = Number(1/length*100).toFixed(7)+'%'
	$('#innerBox').width(num1);
	$('#innerBox').html(skusHtml);
	$('#innerBox li').width(num2);

	function transformBox(obj,value,time,has3d){
		var time=time?time:0;
		transl=has3d?"translate3d("+value+"px,0,0)":"translate("+value+"px,0)";
		obj.css({'-webkit-transform':transl,'-webkit-transition':time+'ms linear'});
	}

	// 滑动
	function slide(tPoint,d){
		//校验"增加"是否合法
		var input = $('h3 input');	
		if(input.length){
			return false;
		}

		var _this = tPoint.self,
			_inner = _this.children(),
			innerW = _inner.width(),
			count = tPoint.count,
			d = d?d:tPoint.direction;
		switch(d){
			case "left":
				--count;
				break;
			case "right":
				++count;
				break;
		}

		if(count == 1){
			count = 0;
		}
		if(count == -tPoint.total){
			count = -tPoint.total+1;
		}
		var offset = (count * innerW/tPoint.total);
		transformBox(_inner,offset,tPoint.speed,tPoint.has3d);
		tPoint.setAttr('count',count);
	}

	// 手势滑动参数
	args = {
		speed:300,
		iniL:30, //x轴方向最小移动距离(才能触发)
		eCallback:function(tPoint){
			console.log(tPoint);
			slide(tPoint);
			// setTimeout("slide("+tPoint+")",3000);
		},
		afterCallback:function(tPoint){
			$('#btn_prev').on('click',function(){
				//判断'增加'数据是否合法
				if($('#default_add').find('input').length){
					$('#default_add input').trigger('focus').css('border','1px solid red');
					return false;
				}
				slide(tPoint,"right");
			});

			$('#btn_next').on('click',function(){
				//判断'增加'数据是否合法
				if($('#default_add').find('input').length){
					$('#default_add input').trigger('focus').css('border','1px solid red');
					return false;
				}
				slide(tPoint,'left');
			});
		}
	}
	$("#touchBox").Swipe(args);	

	// 点击更正
	$("button[name='btn_alter']").on('click',function(e){
		_this = $(this);
		var container = _this.prev().prev();
		if(container.find('input').length){
			var seq = _this.parent().find('h1 label').text();
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

		return false;

	});

	// 点击补货完成
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

	// 对话框点击"修改"
	$('#btn_dia_close').on('click',function(){
		$('#myModal').hide();
	});

	// 对话框点击"保存/确认"
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