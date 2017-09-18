;(function (factory) {
	'use strict';
	// Register as an AMD module, compatible with script loaders like RequireJS.
	if (typeof define === 'function' && define.amd) {
		define(['jquery'], factory);
	}
	else {
		factory(jQuery);
	}
}(function ($, undefined) {
	'use strict';
	var defaults = {
		speed : 5000,
		pageCss : 'pagination',
		auto: true //鑷姩鍒囨崲
	};
	
	var nowImage = 0;//鐜板湪鏄摢寮犲浘鐗�
	var pause = false;//鏆傚仠
	var autoMethod;

	/**
     * @method private
     * @name _init
     * @description Initializes plugin
     * @param opts [object] "Initialization options"
     */
	function _init(opts) {
		opts = $.extend({}, defaults, opts || {});
		// Apply to each element
        var $items = $(this);
        for (var i = 0, count = $items.length; i < count; i++) {
            _build($items.eq(i), opts);
        }
        return $items;
	}
	
	/**
	 * @method private
	 * @name _getSlides
	 * @description 鑾峰彇骞荤伅鐗囧璞�
	 * @param $node [jQuery object] "鐩爣瀵硅薄"
	 */
	function _getSlides($node) {
		return $node.children('li');
	}
	
	/**
	 * @method private
	 * @name _build
	 * @description Builds each instance
	 * @param $node [jQuery object] "鐩爣瀵硅薄"
	 * @param opts [object] "鎻掍欢鍙傛暟"
	 */
    function _build($node, opts) {
		var $slides = _getSlides($node);
		$slides.eq(0).siblings('li').css({'display':'none'});
		var numpic = $slides.size() - 1;
		
		$node.delegate('li', 'mouseenter', function() {
			pause = true;//鏆傚仠杞挱
			clearInterval(autoMethod);
		}).delegate('li', 'mouseleave', function() {
			pause = false;
			autoMethod = setInterval(function() {
				_auto($slides, $pages, opts);
			}, opts.speed);
		});
		//console.log(autoMethod)
		var $pages = _pagination($node, opts, numpic);
		
		if(opts.auto) {
			autoMethod = setInterval(function() {
				_auto($slides, $pages, opts);
			}, opts.speed);
		}
	}
	
	/**
	 * @method private
	 * @name _pagination
	 * @description 鍒濆鍖栭€夋嫨鎸夐挳
	 * @param $node [jQuery object] "鐩爣瀵硅薄"
	 * @param opts [Object] "鍙傛暟"
	 * @param size [int] "鍥剧墖鏁伴噺"
	 */
	 function _pagination($node, opts, size) {
		var $ul = $('<ul>', {'class': opts.pageCss});
		for(var i = 0; i <= size; i++){
			$ul.append('<li>' + '<a href="javascript:void(0)">' + (i+1) + '</a>' + '</li>');
		}
		
		$ul.children(':first').addClass('current');//缁欑涓€涓寜閽€変腑鏍峰紡
		var $pages = $ul.children('li');
		$ul.delegate('li', 'click', function() {//缁戝畾click浜嬩欢
			var changenow = $(this).index();
			_changePage($pages, $node, changenow);
		}).delegate('li', 'mouseenter', function() {
			pause = true;//鏆傚仠杞挱
		}).delegate('li', 'mouseleave', function() {
			pause = false;
		});
		$node.after($ul);
		return $pages;
	 }
	 
	 /**
	 * @method private
	 * @name _change
	 * @description 閫夋嫨涓嶅悓椤甸潰鎸夐挳鏄剧ず涓嶅悓鍥剧墖
	 * @param $pages [jQuery object] "鎸夐挳瀵硅薄"
	 * @param $node [jQuery object] "鐩爣瀵硅薄"
	 * @param changenow [int] "瑕侀€変腑鐨勬寜閽殑涓嬫爣"
	 */
	 function _changePage($pages, $node, changenow){
		var $slides = _getSlides($node);
		_fadeinout($slides, $pages, changenow);
		nowImage = changenow;
	}
	
	 /**
	 * @method private
	 * @name _change
	 * @description 骞荤伅鐗囨樉绀轰笌褰辫棌
	 * @param $slides [jQuery object] "鍥剧墖瀵硅薄"
	 * @param $pages [jQuery object] "鎸夐挳瀵硅薄"
	 * @param next [int] "瑕佹樉绀虹殑涓嬩竴涓簭鍙�"
	 */
	 function _fadeinout($slides, $pages, next){
		$slides.eq(nowImage).css('z-index','2');
		$slides.eq(next).css({'z-index':'1'}).show();
		$pages.eq(next).addClass('current').siblings().removeClass('current');
		$slides.eq(nowImage).fadeOut(400, function(){
			$slides.eq(next).fadeIn(500);
		});
	}
	
	/**
	 * @method private
	 * @name _auto
	 * @description 鑷姩杞挱
	 * @param $slides [jQuery object] "鍥剧墖瀵硅薄"
	 * @param $pages [jQuery object] "鎸夐挳瀵硅薄"
	 * @param opts [Object] "鍙傛暟"
	 */
	 function _auto($slides, $pages, opts){
		var next = nowImage + 1;
		var size = $slides.size() - 1;
		if(!pause) {
			if(nowImage >= size){
				next = 0;
			}
			
			_fadeinout($slides, $pages, next);
			
			if(nowImage < size){
				nowImage += 1;
			}else {
				nowImage = 0;
			}
		}else {
			clearInterval(autoMethod);//鏆傚仠鐨勬椂鍊欏氨鍙栨秷鑷姩鍒囨崲
		}
	 }
	
	
	$.fn.jslide = function (method) {
		return _init.apply(this, arguments);
    };
}));