{*
abce003fa64d4f78a29f3833ed1b01c084590352, v4 (xcart_4_6_0), 2013-04-11 18:14:44, modules_banner.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
<div id="modules_banner">
<script type="text/javascript">
//<![CDATA[
{literal}
$(document).ready(function () {
  var offset = $('#modules-tabs-container').offset().top
               + $('ul.ui-tabs-nav').outerHeight()
               - parseInt($('.banner-tools').css('padding-top'), 10);
  $('.banner-tools').offset({ top: offset});
  $('#banner_close_link').click(function(){
    var date_time = new Date().getTime() + 3600*24*1000;
    $.cookie('hide_dialog_xcart_paid_modules', '1', { expires: new Date(date_time)});
  });
});
{/literal}
//]]>
</script>
<div id="banner_close_link">
<a href="javascript: void(0);" onclick="javascript: $('.banner-tools').hide(); return false;"></a>
</div>
<div id="xcart_paid_modules">
<iframe id="ac434a45" name="ac434a45" src="//ads.qtmsoft.com/www/delivery/afr.php?zoneid=12&amp;cb=4561"
frameborder="0" scrolling="no" width="210" height="400"><a href="//ads.qtmsoft.com/www/delivery/ck.php?n=af232f2e&amp;cb=4561" target="_blank"><img src="//ads.qtmsoft.com/www/delivery/avw.php?zoneid=12&amp;cb=4561&amp;n=af232f2e" border="0" alt="" /></a></iframe>
</div>
<div id="more_xcart_modules">
<a href="http://www.x-cart.com/modules.html?utm_source=xcart&amp;utm_medium=xcart_modules_link&amp;utm_campaign=xcart_modules" target="_blank">{$lng.lbl_more_xcart_modules}</a>
</div>
</div>
