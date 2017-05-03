"use strict";
(function(){
	var scw = $(window).width(),
		scw = scw > 435 ? 435 : scw,
		fz = scw / 10.8;
	$('html').css('font-size', fz);
	$('body').css('font-size', fz);
}());

$.fn.listHandler = function(){
	var _this = $(this);
	_this.find('a').on('click', function(event){
		var _me = $(this),
			next = _me.next();
		if(_me.hasClass('multi')){
			event.preventDefault();
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
		html = "<p>暂无匹配售货机</p>";
	}
	return html;
};

//图片加载
$.fn.imgLoading = function(){
	
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
		refreshCountOfCart({
			pid: pid,
			pname: plist[pid]['pname'],
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

$.fn.detailHandler = function(o){
	var selected = window.sessionStorage['selectedProducts'],
		pid = o.pid,
		pname = o.pname,
		oprice = o.oprice,
		price = o.price,
		left = o.left;
	selected = JSON.parse(selected);
	refreshNumbersForDetail(selected, pid);


	$('#addToCart').on('click', function(){
		refreshCountOfCart({
			pid: pid,
			pname: pname,
			oprice: oprice,
			rprice: price,
			left: left
		}, selected);

		refreshNumbersForDetail(selected, pid);
		window.sessionStorage['selectedProducts'] = JSON.stringify(selected);
	});
};

$.fn.accountHandler = function(){
	var selected = window.sessionStorage['selectedProducts'],
		html = '';
	selected = JSON.parse(selected);
};

function refreshNumbersForDetail(selected, pid){
	var btns = $('.detailButtons'),
		total = selected['total'];
	if(total){
		$('#totalSelected').text(total);
		$('#totalCart').text(total).show();
	}

	if(selected['products'][pid] && selected['products'][pid]['count'] < selected['products'][pid]['left'] ){
		btns.hide().eq(0).show();
	}else{
		btns.hide().eq(1).show();
	}
}

function refreshCountOfCart(o, selected){
	var pid = o.pid,
		pname = o.product_name,
		oprice = o.oprice,
		price = o.rprice,
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