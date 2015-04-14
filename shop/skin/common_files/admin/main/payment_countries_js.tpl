{*
vim: set ts=2 sw=2 sts=2 et:
*}
<script type="text/javascript">
//<![CDATA[
var payment_countries = new Array();
{foreach from=$payment_countries.processors key=processor item=item}
payment_countries['{$processor}'] = [
  {foreach from=$item key=key item=code}
  '{$code}',
  {/foreach}
]
{/foreach}

var default_banner_id = {$payment_banners.DEFAULT};
var payment_banners = new Array();
{foreach from=$payment_banners key=key item=item}
  payment_banners.{$key} = {$item};
{/foreach}

var stored_payment_gateways = document.getElementsByName('processor')[0].cloneNode(true); 
//]]>
</script>
<script type="text/javascript" src="{$SkinDir}/admin/js/payment_countries.js"></script>
