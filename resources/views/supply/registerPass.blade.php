<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
	<meta name="format-detection" content="telephone=no"/>
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta http-equiv="pragma" content="no-cache" />
	<title>注册申请提交成功</title>
	<link href="/sources/style/bootstrap.min.css" type="text/css" rel="stylesheet" />
	<style>
		body{
			background: #EEEEEE;
		}
		.jumbotron{
			margin:30px;
			position: relative;
			top: 150px;
		}
		
	</style>
</head>
<body>
	<div class="jumbotron">
	  <h1>注册申请提交成功!</h1>
	  <p>您的申请已通过邮件方式提交,审核通过后可以开始补货</p>
	  <p style="float:right"><button class="btn btn-primary btn-lg" href="#" role="button">我知道了</button></p>
	</div>
	<script type="text/javascript" src="/sources/scripts/lib/jquery-3.2.1.min.js"></script>
	<script>
		$('.btn-primary').on('click',function(){
			window.location.href="about:blank";
		});
	</script>
</body>