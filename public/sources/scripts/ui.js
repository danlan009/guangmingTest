"use strict";
(function(){
	var scw = $(window).width(),
		scw = scw > 435 ? 435 : scw,
		fz = scw / 10.8;
	$('html').css('font-size', fz);
	$('body').css('font-size', fz);
}());

var msgArr = {
	'phone_error': '手机号码格式不正确，请重新输入',
	'phone_empty': '手机号码不能为空',
	'noVms': '暂无匹配售货机'
};

$.fn.listHandler = function(){
	var _this = $(this);
	_this.find('a').on('click', function(event){
		var _me = $(this),
			next = _me.next();
		if(_me.hasClass('multi')){
			event.preventDefault();
			_this.find('span').removeClass('on');
			next.toggleClass('on');
			if(next.hasClass('on')){
				_me.addClass('on');
			}else{
				_me.removeClass('on');
			}
		}
	});
};

// 搜索事件
$.fn.searchVms = function(vms){

	var _this = $(this), 
		keywords = '', 
		html='', 
		result = $('#search_result'),
		inputBox = _this.next(),
		list = result.prev(),
		btnClear = $('#btnClearText');

	_this.on('click', function(){
		keywords = inputBox.val();
		if($.trim(keywords).length){
			html = _this.searchVmsHandler(keywords, vms);
			result.html(html).show();
			list.hide();
		}
	});

	// 软键盘搜索事件
	inputBox.on('keyup', function(event){
		var key = event.keyCode | event.which;
		if(event.keyCode === 13){
			keywords = inputBox.val();
			if($.trim(keywords).length){
				html = _this.searchVmsHandler(keywords, vms);
				result.html(html).show();
				list.hide();
			}
		}
	});

	inputBox.on('input', function(){
		var _this = $(this);
		if(_this.val().length==0){
			result.hide();
			list.show();
			btnClear.hide();
		}else{
			btnClear.show();
		}

	});

	btnClear.on('click', function(){
		inputBox.val('');
		result.hide();
		list.show();
	});
};

// 搜索售货机方法
$.fn.searchVmsHandler = function(keywords, vms){
	var vm_name = '', html = '', vmid = '';
	for(var i=0,len=vms.length; i<len; i++){
		vm_name = vms.eq(i).attr('data-name');
		vmid = vms.eq(i).attr('data-vmid');
		if(vm_name.indexOf(keywords) != -1){
			html += '<a href="/wx/list/'+vmid+'">'+vm_name+'</a>';
		}
	}
	if(html.length==0){
		html = "<p>"+msgArr.noVms+"</p>";
	}
	return html;
};

$.fn.loadImages = function(){
	var _this = $(this), src=[], newImg=[];
	for(var i=0,len=_this.length; i<len; i++){
		(function(i){
			src[i]=_this.eq(i).attr('data-src');
			if(src[i]){
				newImg[i] = new Image();
				newImg[i].src= src[i];
				newImg[i].onload = function(){
					_this.eq(i).attr('src', src[i]);
				};
			}
			
		})(i);
	}
};

//单击“预定”按钮
$.fn.addToCart = function(elm){
	var _this = $(this), _elm = elm, oldCount=0, 
		plist = window.sessionStorage.getItem('productsListObj'),
		plist = JSON.parse(plist),
		selected = window.sessionStorage['selectedProducts'] ? window.sessionStorage['selectedProducts'] : '',
		selected = selected ? JSON.parse(selected) : null,
		oneElm = '';
	if(selected && selected['total']){
		_elm.text(selected['total']).addClass('show');
		for(var key in selected['products']){
			oneElm = $('.p_'+key);
			oneElm.text('预定('+selected['products'][key]['count']+')').removeClass('blue_button');
			if(selected['products'][key]['count'] < selected['products'][key]['left']){
				oneElm.addClass('green_button').removeClass('grey_button');
			}else{
				oneElm.addClass('grey_button').removeClass('green_button');
			}
		}
	}
	
	_this.on('click', function(){
		var _me = $(this), pid=_me.attr('data-id');
		if(_me.hasClass('grey_button')){
			return false;
		}
		console.log('--------------')
		console.log(plist[pid])

		refreshCountOfCart({
			pid: pid,
			pname: plist[pid]['pname'],
			volume: plist[pid]['volume'],
			oprice: plist[pid]['oprice'],
			rprice: plist[pid]['rprice'],
			left: plist[pid]['left'] // 剩余数量
		}, selected);

		_elm.text(selected['total']).addClass('show');
		_me.text('预定('+selected['products'][pid]['count']+')').removeClass('blue_button');
		if(selected['products'][pid]['count'] < selected['products'][pid]['left']){
			_me.addClass('green_button').removeClass('grey_button');
		}else{
			_me.addClass('grey_button').removeClass('green_button');
		}
		window.sessionStorage['selectedProducts'] = JSON.stringify(selected);
		_me.append('<span></span>');
	});
};

$.fn.loadMoreProducts = function(size){
	var _this = $(this),
		startY = 0,
		posY = 0,
		height = _this.height(),
		_size = parseInt(size);

	_this.on('touchstart', function(e){
		startY = e.changedTouches[0].pageY;
	});

	_this.on('touchmove', function(e){
		var touches = e.changedTouches[0];
		posY = touches.pageY < startY ? startY- touches.pageY : 0;
		if(height + posY < height * 1.5){
			_this.css({
				'height': (height + posY) + 'px'
			});
		}else{
			_this.addClass('loading');
		}
	});

	_this.on('touchend', function(e){
		// 对列表的处理
		var list = $('.pro').not('.show');
		list.each(function(key, value){
			if(key < _size){
				$(this).addClass('show');
			}
		});
		_this.removeClass('loading');

	});
};

$.fn.detailHandler = function(o){
	var selected = window.sessionStorage['selectedProducts'],
		pid = o.pid,
		pname = o.pname,
		oprice = o.oprice,
		price = o.price,
		left = o.left;
	selected = JSON.parse(selected);
	refreshNumbersForDetail(selected, pid, left);


	$('#addToCart').on('click', function(){
		refreshCountOfCart({
			pid: pid,
			pname: pname,
			oprice: oprice,
			rprice: price,
			left: left
		}, selected);

		refreshNumbersForDetail(selected, pid, left);
		window.sessionStorage['selectedProducts'] = JSON.stringify(selected);
	});
};

$.fn.accountHandler = function(){
	var selected = window.sessionStorage['selectedProducts'],
		html = '',
		_this = $(this);
	selected = JSON.parse(selected);
	console.log(selected)
	_this.text(selected['total']).show();

};

$.fn.selectedHandler = function(){
	var _this = $(this),
		html = '',
		selected = window.sessionStorage['selectedProducts'],
		selected = JSON.parse(selected),
		products = selected['products'];

	for(var key in products){
		var xx = products[key].rprice / 100,
			_c = products[key]['count'] >= products[key]['left'];
		if(products[key]['count']){
			html += '<div class="product"><section><span>'
				 + '<img src="/sources/images/products/100017_l.jpg" />'
				 + '</span></section>'
				 + '<h1>'+products[key].pname
				 + ( !products[key].volume ? '' : '<span>('+products[key].volume+')</span>' ) + '</h1>'
				 + '<p>￥'+(products[key].rprice / 100)+'</p>'
				 + '<h3 class="changeCount" data-id="'+key+'" >每日配送:<span>'
				 + '<button type="button" class="btn_r"></button>'
				 + '<input type="tel" value="'+products[key]['count']+'" class="text_box '+(products[key]['count'] > products[key]['left']?'error':'')+'" />'
				 + '<button type="button" class="btn_a '+(_c?'grey':'')+'" '+(_c?'disabled="disabled"':'')+' ></button>'
				 + '</span></h3></div>';
		}
	}
	_this.html(html);
};

$.fn.computerTotalPrice = function(){
	var _this = $(this),
		selected = window.sessionStorage['selectedProducts'],
		total = 0, 
		products;
	if(!selected){ return false; }
	selected = JSON.parse(selected);
	console.log(selected)
	products = selected['products'];
	total = computerTotal(products);
	_this.text('￥' + total);
};

$.fn.radioBox = function(tagname, type){
	var _this = $(this);
	_this.find(tagname).on('click', function(){
		var _me = $(this);
		_me.addClass('on').siblings().removeClass('on');

		if(type == 'card'){
			collectionsObj.card_id = _me.attr('data-card-id');
			collectionsObj.card_name = _me.attr('data-card-name');
		}else if(type == 'week'){
			collectionsObj.rate = _me.attr('data-id');
		}else if(type == 'days'){
			collectionsObj['type'] = _me.attr('data-num');
		}
		console.log(collectionsObj);
	});
};

// 检查是否国内手机号
$.fn.checkMobilePhone = function(){
	var _this = $(this),
		pattern = /^1[3-9]{1}[0-9]{9}$/,
		parent = _this.parent('.phone'),
		msg = $('#msg'),
		clearBtn = _this.find('button').eq(0);
	_this.on('change', function(){
		var _txt = _this.val();
		collectionsObj.phone = '';
		if(!_txt.length){
			parent.addClass('error');
			msg.text(msgArr.phone_empty);
		}else if(pattern.test(_txt)){
			parent.removeClass('error');
			msg.text('');
			collectionsObj.phone = _txt;
		}else{
			parent.addClass('error');
			msg.text(msgArr.phone_error);
		}
		console.log(collectionsObj)
	});
};

// 购物车控制商品数量
$.fn.selectedCountHandler = function(){
	console.log('-----------------');
	var totalPrice = $('#totalPrice'),
		cartProductsAccount = $('#cartProductsAccount');

	$(this).each(function(key, value){
		console.log(key);
		console.log(value);

		var _this = $(this),
			btn_reduce = _this.find('.btn_r'),
			btn_add = _this.find('.btn_a'),
			text_box = _this.find('.text_box'),
			pid = _this.attr('data-id');

		// 单击增加按钮
		btn_add.on('click', function(){
			var _me = $(this), result;
			result = checkProductsCount(pid, 'add', null);
			console.log(result)
			text_box.val(result['count']);
			if(result['result']){
				// 置灰
				_me.addClass('grey');
				_me.attr('disabled', 'true');
			}else{
				_me.removeClass('grey');
				_me.removeAttr('disabled');
			}

			totalPrice.text('￥'+result['totalPrice']);
			cartProductsAccount.text(result['totalCount']);
			buttonStyle(result['result'], {
				'reduce': btn_reduce,
				'add': btn_add,
				'text': text_box
			});
		});

		// 单击减少按钮
		btn_reduce.on('click', function(){
			var _me = $(this), result;
			result = checkProductsCount(pid, 'reduce', null);
			text_box.val(result['count']);
			console.log(result);
			if(result['result'] == 1 || result['result'] == 4){
				// 置灰
				_me.addClass('grey');
				_me.attr('disabled', 'true');
				if(result['result'] == 1){
					_this.parent('.product').hide();
				}
			}else{
				_me.removeClass('grey');
				_me.removeAttr('disabled');
			}

			totalPrice.text('￥'+result['totalPrice']);
			cartProductsAccount.text(result['totalCount']);
			buttonStyle(result['result'], {
				'reduce': btn_reduce,
				'add': btn_add,
				'text': text_box
			});
		});

		// 修改文本框
		text_box.on('change', function(){
			var _me = $(this), result, _text = _me.val();
			if(!/^\d+$/.test(_text)){
				_me.addClass('error');
				return false;
			}
			result = checkProductsCount(pid, 'text', _text);
			if(result['result'] == 3 || result['result'] == 0){
				_me.addClass('error');
			}else{
				_me.removeClass('error');
			}

			totalPrice.text('￥'+result['totalPrice']);
			cartProductsAccount.text(result['totalCount']);
			buttonStyle(result['result'], {
				'reduce': btn_reduce,
				'add': btn_add,
				'text': text_box
			});
		});

	});
};

$.fn.weixinPay = function(){
	var _this = $(this);

	_this.on('click', function(){
		// 检查用户的信息是否都合法
		if(!collectionsObj.phone){ 
			$('#msg').text(msgArr.phone_error);
			return false; 
		}
		if($('.changeCount').find('.error').length){return false;}

		var data, selected;
		selected = window.sessionStorage['selectedProducts'];
		if(!selected){ return false; }
		selected = JSON.parse(selected);
		collectionsObj.products = selected.products;

		console.log(collectionsObj);

		$.ajax({
			url: '/wx/ajax_check_wxpay',
			type: 'get',
			data: collectionsObj,
			success: function(data){
				console.log(data);
				var d = JSON.parse(data);
				if(d.code === 200){
					$.ajax({
						url: '/wx/ajax_prepay?test=laiguangying',
						type: 'get',
						success: function(data){
							var result = JSON.parse(data);
							// 调起微信支付
							if(result.code == 200){
								// 跳转到预定结果页面
								window.location.href = '/wx/result/'+result.wxTxnId;
							}
						}
					});
				}
			}
		});
	});
};

// 控制增加 减少 输入框样式
function buttonStyle(result, elmsObj){
	// 1: 用户预定数量正常
	// 2: 用户预定数量与剩余量一样
	// 3: 用户预定数量大于剩余量
	// 4: 用户预定数量小于0
	switch(result){
		case 0:
			elmsObj['text'].removeClass('error');
			elmsObj['reduce'].removeClass('grey').removeAttr('disabled');
			elmsObj['add'].removeClass('grey').removeAttr('disabled');
			break;
		case 2:
			elmsObj['text'].removeClass('error');
			elmsObj['reduce'].removeClass('grey').removeAttr('disabled');
			elmsObj['add'].addClass('grey').attr('disabled','disabled');
			break;
		case 3:
			elmsObj['text'].addClass('error');
			elmsObj['reduce'].removeClass('grey').removeAttr('disabled');
			elmsObj['add'].removeClass('grey').removeAttr('disabled');
			break;
	}
}

// 计算商品总价
function computerTotal(products){
	var total = 0;

	for(var i in products){
		total += products[i].count * products[i].rprice;
	}

	total = total / 100;
	return total;
}

// 检查商品数量是否超出
function checkProductsCount(pid, type, text){
	var selected = window.sessionStorage['selectedProducts'],
		products,
		result,
		oldCount = 0;

	selected = JSON.parse(selected);
	products = selected['products'];
	oldCount = products[pid]['count'];

	switch(type){
		case "add":
			selected['total']++;
			products[pid]['count']++;
			break;
		case "reduce":
			selected['total']--;
			products[pid]['count']--;
			break;
		case "text":
			var _count = parseInt(text);
			selected['total'] += _count - oldCount;
			products[pid]['count'] = _count;
			break;
	}

	selected['total'] = selected['total'] < 0 ? 0 : selected['total'];

	console.log(products[pid]['count'])
	console.log(products[pid]['left'])

	if(products[pid]['count'] == 0 ){
		result = 1;
		delete products[pid];
	}else if(products[pid]['count'] == products[pid]['left']){
		result = 2;
	}else if(products[pid]['count'] > products[pid]['left']){
		result = 3;
	}else if(products[pid]['count'] < 0){
		result = 4;
	}else{
		result = 0;
	}

	window.sessionStorage['selectedProducts'] = JSON.stringify(selected);

	// 1:0
	// 2:与left值一样
	// 3:大于left的值
	// 4:小于0

	return {
		'pid': pid,
		'type': type,
		'count': result!=1 ? products[pid]['count'] : 0,
		'result':  result,
		'totalPrice': computerTotal(selected['products']),
		'totalCount': selected['total']
	}
}

function refreshNumbersForDetail(selected, pid, left){
	var btns = $('.detailButtons'),
		total = selected['total'];
	if(total){
		$('#totalSelected').text(total);
		$('#totalCart').text(total).show();
	}

	if(left > 0){
		if(!selected['products'][pid]){
			btns.hide().eq(0).show();
		}else if( selected['products'][pid]['count'] < selected['products'][pid]['left'] ){
			btns.hide().eq(0).show();
		}else{
			btns.hide().eq(1).show();
		}
	}
	
}

function refreshCountOfCart(o, selected){
	console.log(o)
	var pid = o.pid,
		pname = o.pname,
		oprice = o.oprice,
		price = o.rprice,
		volume = o.volume,
		left = o.left;

	if(!selected || !selected['products']){
		selected = {
			products: {},
			total: 0
		}
	}

	if(selected['products'] && selected['products'][pid]){
		selected['products'][pid]['count'] += 1;
	}else{
		selected['products'][pid] = {
			'pid': pid,
			'pname': pname,
			'oprice': oprice,
			'rprice': price,
			'volume': volume,
			'count': 1,
			'left': left
		}
	}

	if(selected['total']){
		selected['total'] += 1;
	}else{
		selected['total'] = 1;
	}
	return selected;
}