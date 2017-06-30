<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
	<meta name="format-detection" content="telephone=no"/>
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta http-equiv="pragma" content="no-cache" />
	<title>填写注册个人信息</title>
	<link href="<?php echo $cdn_url ?>style/bootstrap.min.css" type="text/css" rel="stylesheet" />
	<style>
		body{
			background: #EEEEEE;
		}
		.container{
			padding: 40px;
			width:60%;
			position:relative;
			top:200px;
			background: white;
		}

		.btn_sub{
			position: relative;
			left:100px;
			top:20px;
		}
		
	</style>
</head>
<body>
	<div class="container">
		<form class="form-horizontal" method="post" action="{{url('supply/register_resolve')}}">
		  <div class="form-group">
		    <label for="inputEmail3" class="col-sm-2 control-label">姓名</label>
		    <div class="col-sm-10">
		      <input type="text" class="form-control" id="" placeholder="" name="name">
		    </div>
		  </div>
		  <div class="form-group">
		    <label for="inputPassword3" class="col-sm-2 control-label">手机号</label>
		    <div class="col-sm-10">
		      <input type="text" class="form-control" id="" placeholder="" name="phone">
		      <!-- 加密openid 字符串-->
		      <input type="hidden" name="encrypt" value="{{$encrypt}}">
		      <!-- csrf 验证 -->
		      <input type="hidden" name="_token" value="{{ csrf_token() }}" />
		    </div>
		  </div>
		  
		  <div class="form-group sub-button">
		    <div class="col-sm-offset-2 col-sm-10 btn_sub" >
		      <button type="submit" class="btn btn-default" style="margin-right:50px">取消</button>
		      <button type="submit" class="btn btn-primary">确定</button>
		    </div>
		  </div>
		</form>

	</div>
	
</body>