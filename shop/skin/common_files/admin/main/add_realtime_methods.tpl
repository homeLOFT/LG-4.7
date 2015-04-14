{*
b696a189468712da31ff4313eb149927e8755f78, v3 (xcart_4_6_5), 2014-08-26 16:09:23, add_realtime_methods.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{include file="page_title.tpl" title=$lng.lbl_add_remove_shipping_methods}

{if $config.Shipping.enable_shipping ne "Y"}

{capture name=dialog}

<br />

{$lng.txt_shipping_disabled}

<br />

{/capture}
{include file="dialog.tpl" title=$lng.lbl_shipping_methods content=$smarty.capture.dialog extra='width="100%"'}

{else}

<br />

<script type="text/javascript" src="{$SkinDir}/js/change_all_checkboxes.js"></script>
<script type="text/javascript" language="JavaScript 1.2">//<![CDATA[
checkboxes_form = 'shippingmethodsform';
//]]></script>

{capture name=dialog}

<br />

<form action="shipping.php" method="post" name="shippingmethodsform">
<input type="hidden" name="carrier" value="{$carrier|escape}" />
<input type="hidden" name="mode" value="add_realtime_methods" />

<script type="text/javascript">//<![CDATA[
var expands = new Array({foreach from=$carriers item=car}'{$car.code}',{/foreach}'');
{literal}
function expand_all(flag) {
  var x;
  for (x = 0; x < expands.length; x++) {
    if (expands[x].length == 0)
      continue;
      var elm1 = document.getElementById("open"+expands[x]);
    var elm2 = document.getElementById("close"+expands[x]);
    var elm3 = document.getElementById("box"+expands[x]);

    if(!elm3 || !elm1 || !elm2)
      continue;

    if (!flag) {
      elm1.style.display = "none";
      elm2.style.display = "";
      elm3.style.display = "none";
    } else {
      elm1.style.display = "";
      elm2.style.display = "none";
      elm3.style.display = "";
    }
  }
}
{/literal}
//]]></script>

<table cellpadding="2" cellspacing="1" width="100%">

{if $carriers ne ''}

<tr>
  <td colspan="2">
  <div align="right" style="line-height:170%"><a href="javascript:expand_all(true);">{$lng.lbl_expand_all}</a> / <a href="javascript:expand_all(false);">{$lng.lbl_collapse_all}</a></div>
  </td>
</tr>

{foreach from=$carriers item=car}
<tr class="TableSubHead">
  <td colspan="2">
    {include file="main/visiblebox_link.tpl" mark=$car.code title=$car.shipping}
  </td>
</tr>
<tr id="box{$car.code}" style="display: none;">
  <td colspan="2">

<div style="line-height:170%">{if $car.code ne 'USPS' || $allow_check_all_for_usps}<a href="javascript:void(0);" onclick="change_all(true,false,new Array({foreach from=$shipping item=v key=k}{if $v.code eq $car.code}'data[{$v.shippingid}][active]',{/if}{/foreach}''));">{$lng.lbl_check_all}</a> / {/if}<a href="javascript:void(0);" onclick="change_all(false,false,new Array({foreach from=$shipping item=v key=k}{if $v.code eq $car.code}'data[{$v.shippingid}][active]',{/if}{/foreach}''));">{$lng.lbl_uncheck_all}</a></div>

<table cellpadding="2" cellspacing="1" width="100%">
<tr class="TableHead">
  <td width="50">{$lng.lbl_active}</td>
  <td>{$lng.lbl_shipping_method}</td>
  <td>{$lng.lbl_destination}</td>
</tr>

{foreach from=$shipping item=s}
{if $s.code eq $car.code and $s.is_new ne 'Y'}

<tr{cycle values=", class='TableSubHead'"}>
  <td align="center"><input type="checkbox" name="data[{$s.shippingid}][active]" value="Y"{if $s.active eq "Y"} checked="checked"{/if} /></td>
  <td>{$s.shipping|trademark}</td>
  <td align="center">{if $s.destination eq "L"}{$lng.lbl_national}{else}{$lng.lbl_international}{/if}</td>
</tr>

{/if}
{/foreach}

</table>
<br />
  </td>
</tr>
{/foreach}

<tr>
  <td colspan="2">
{if $carrier ne ''}
<script type="text/javascript">//<![CDATA[
visibleBox('{$carrier}');
//]]></script>
{/if}
  </td>
</tr>
{/if}

<tr>
  <td>&nbsp;</td>
</tr>

<tr>
  <td colspan="3" class="main-button">
    <div id="sticky_content">
      <input type="submit" value="{$lng.lbl_apply_changes|strip_tags:false|escape}" class="big-main-button" />
    </div>
  </td>
</tr>


</table>

</form>

<br /><br />

{/capture}
{include file="dialog.tpl" title='' content=$smarty.capture.dialog extra='width="100%"'}

{/if}
