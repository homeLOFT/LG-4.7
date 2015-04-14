{*
e66af9def9eb9914ed35b2e42b34821337ba81e2, v3 (xcart_4_5_4), 2012-10-24 13:39:58, init.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
<script type="text/javascript">
//<![CDATA[
var allowed_cookies = [];
{foreach from=$allowed_cookies item=v key=k}
allowed_cookies['{$k}'] = '{$v}';
{/foreach}
var lbl_sec = ' {$lng.lbl_sec}';
//]]>
</script>
{load_defer file="modules/EU_Cookie_Law/func.js" type="js"}
