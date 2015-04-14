{*
7ca20ac64e17c762b84ce0380e3656819ee54adf, v6 (xcart_4_6_0), 2013-05-07 14:45:49, modules_thirdparty.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
<div id="modules-thirdparty-note">{$lng.txt_modules_thirdparty_note}</div>
<ul class="modules-list extensions">
{foreach from=$thirdparty_banners item=banner}
{assign var=cb value=1|mt_rand:99999999999}
<li>
  <iframe id="{$banner.n}" name="{$banner.n}" src="//ads.qtmsoft.com/www/delivery/afr.php?zoneid={$banner.zoneid}&amp;cb={$cb}" frameborder="0" scrolling="no"><a href="//ads.qtmsoft.com/www/delivery/ck.php?n={$banner.n}&amp;cb={$cb}" target="_blank"><img src="//ads.qtmsoft.com/www/delivery/avw.php?zoneid={$banner.zoneid}&amp;cb={$cb}&amp;n={$banner.n}" border="0" alt="" /></a></iframe>
</li>
{/foreach}
</ul>
<div id="more-thirdparty-modules">
{include file="buttons/button.tpl" button_title=$lng.lbl_more_thirdparty_modules href="http://marketplace.x-cart.com/addons-modules/?utm_source=xcart&amp;utm_medium=thirdparty_modules_link_bottom&amp;utm_campaign=xcart_modules" substyle="thirdparty-modules"}
</div>
