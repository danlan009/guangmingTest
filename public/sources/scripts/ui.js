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

	// TODO:
	// 显示用户选择的商品
	// 单击 + - 按钮操作, 底部总价变化
	// 提交下单
	// 

};

$.fn.selectedHandler = function(){
	var _this = $(this),
		html = '',
		selected = window.sessionStorage['selectedProducts'],
		selected = JSON.parse(selected),
		products = selected['products'];

	for(var key in products){
		var xx = products[key].rprice / 100;
		html += '<div class="product"><section><span>'
			 + '<img src="/sources/images/products/100017_l.jpg" />'
			 + '</span></section>'
			 + '<h1>'+products[key].pname
			 + ( !products[key].volume ? '' : '<span>('+products[key].volume+')</span>' ) + '</h1>'
			 + '<p>￥'+(products[key].rprice / 100)+'</p>'
			 + '<h3 class="changeCount">每日配送:<span>'
			 + '<button type="button"></button><input type="text" value="'+products[key]['count']+'" /><button type="button"></button>'
			 + '</span></h3></div>'
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

	for(var i in products){
		total += products[i].count * products[i].rprice;
	}

	total = total / 100;

	_this.text('￥' + total);
};

$.fn.radioBox = function(tagname){
	var _this = $(this);
	_this.find(tagname).on('click', function(){
		var _me = $(this);
		_me.addClass('on').siblings().removeClass('on');
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

		if(pattern.test(_this.val())){
			parent.removeClass('error');
			msg.text('');
		}else{
			parent.addClass('error');
			msg.text(msgArr.phone_error);
		}
	});
};

// $.fn.addressHandler = function(obj){
// 	var _this = $(this),
// 		vmList = JSON.parse(obj['vmList']),
// 		elm = obj['selectVm'];

// 	// 选择点位
// 	_this.on('change', function(){
// 		// 更新售货机选项
// 		console.log('点位切换')
// 		var _me = $(this),
// 			_id = _me.val(),
// 			html = '',
// 			vms = vmList[_id]['vms'];

// 		console.log('点位id:'+_id)
// 		console.log(vms);

// 		for(var i=0, len=vms.length; i<len; i++){
// 			html = '<option value="'+vms[i][vmid]+'">' + vms[i][vm_name] + '</option>';
// 		}

// 		console.log(html)

// 	});
// };

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