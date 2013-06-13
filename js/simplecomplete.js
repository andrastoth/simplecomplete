/*!
 * simpleComplete v1.0.0 jQuery plugin
 * http://atandrastoth.co.uk/webdesign/
 * Author : Tóth András
 * Copyright 2013 Tóth András
 * Released under the MIT license
 * Date: 01 Jun 13 2013
 */
(function($) {
	$.fn.simpleComplete = function(options) {
		if (!this.length) {
			return this;
		}
		var list = {};
		var input = $(this);
		var o = $.extend(true, {}, $.fn.simpleComplete.defaults, options);
		input.wrap('<div class = "auto">');
		var obj = input.parent('div');
		obj.append('<img src="css/img/search.png">');
		var myimg = input.children('img');
		myimg.load(function() {
			obj.width(obj.width() + myimg.width());
		})
		this.each(function() {
			var $this = $(this);
			input.on({
				keyup: function(e) {
					//e.preventDefault();
					if (e.keyCode == 40 || e.keyCode == 38 || e.keyCode == 13) {
						return;
					}
					if (typeof over != 'undefined') {
						over.remove();
					}
					if ($(this).val().length == o.charLimit && typeof list.length == 'undefined') {
						list = ajaxCall(o.getkey, $(this).val());
					} else if ($(this).val() == '') {
						list = {};
					}
					over = $('<ul class = "over">');
					for (var i = 0; i < list.length; i++) {
						var ind = list[i].elem.toLowerCase().indexOf($(this).val().toLowerCase());
						if (ind != -1) {
							over.append('<li><a>' + replaceAll(list[i].elem, ind) + '</a></li>');
						}
					};
					if (over.children().length > 0) {
						obj.append(over);
						over.css({
							top: obj.outerHeight(),
							left: obj.css('border-width').match(/(\d)/g) * -1
						});
					}
				},
				keydown: function(e) {
					if (e.keyCode == 40 || e.keyCode == 38) {
						ind = over.children('.hover').index();
						e.keyCode == 40 ? ind++ : ind--;
						if (ind == -1 && e.keyCode == 38) {
							ind = over.children('li').length - 1
						} else if (ind == over.children('li').length && e.keyCode == 40) {
							ind = 0
						}
						over.children('.hover').removeClass();
						over.children('li').eq(ind).addClass('hover');
						over.scrollTop(over.scrollTop() + over.children('.hover').position().top);
					} else if (e.keyCode == 13 && obj.children('ul').length != 0) {
						input.val(over.children('.hover').children('a').text());
						over.remove();
					} else if (e.keyCode == 13 && obj.children('ul').length == 0) {
						ajaxCall(o.getdata, input.val());
					}
				}
			});

			obj.on({
				click: function() {
					input.val($(this).children('a').text());
					over.remove();
				}
			}, 'li');
			obj.on({
				click: function() {
					ajaxCall(o.getdata, input.val());
				}
			}, 'img');

			function move(e) {
				ind = over.children('.hover').index();
				e.keyCode == 40 ? ind++ : ind--;
				if (ind == -1 && e.keyCode == 38) {
					ind = over.children('li').length - 1
				} else if (ind == over.children('li').length && e.keyCode == 40) {
					ind = 0
				}
				over.children('.hover').removeClass();
				over.children('li').eq(ind).addClass('hover');
				over.scrollTop(over.scrollTop() + over.children('.hover').position().top);
				return;
			}

			function replaceAll(str, n) {
				return str.substring(0, n) + '<strong>' + str.substring(n, n + input.val().length) + '</strong>' + str.substring(n + input.val().length, str.length);
			}

			function ajaxCall(order, param) {
				var retVal;
				$.ajax({
					url: o.php,
					type: 'POST',
					dataType: 'xml/html/script/json/jsonp',
					data: {
						order: order,
						param: param.toLowerCase()
					},
					async: false,
					complete: function(data, xhr, textStatus) {
						if (order == o.getdata) {
							retVal = data.responseText;
							o.resultFunction(retVal);
							over.remove();
						} else {
							retVal = $.parseJSON(data.responseText);
						}
					},
					success: function(data, textStatus, xhr) {

					},
					error: function(xhr, textStatus, errorThrown) {

					}
				});
				return retVal;
			}

		});

		return this;
	};
	$.fn.simpleComplete.defaults = {
		php: 'com.php',
		getkey: 'getkey',
		getdata: 'getdata',
		charLimit: 1,
		resultFunction: function(data) {
			$('#container').html(data);
		}
	};
})(jQuery);