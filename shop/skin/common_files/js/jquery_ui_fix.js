/* http://bugs.jqueryui.com/ticket/8637 */

$.fn.__tabs = $.fn.tabs;
$.fn.tabs = function (a, b, c, d, e, f) {
	var base = location.href.replace(/#.*$/, '');
	$('ul>li>a[href^="#"]', this).each(function () {
		var href = $(this).attr('href');
		$(this).attr('href', base + href);
	});
	$(this).__tabs(a, b, c, d, e, f);
};
