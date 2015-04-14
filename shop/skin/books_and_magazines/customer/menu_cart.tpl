{*
e1db5cf03524aef7b3d94390d4b4baa6311fd42b, v2 (xcart_4_5_5), 2013-02-07 17:35:38, menu_cart.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{if $config.General.ajax_add2cart eq 'Y' and $main ne 'cart' and $main ne 'checkout'}
  {include file="customer/ajax.minicart.tpl" _include_once=1}
  {load_defer file="js.js" type="js"}
{/if}

{capture name=menu}

<div class="minicart-block">
<table cellspacing="0" cellpadding="0">
<tr>
	<td><img src="{$ImagesDir}/spacer.gif" class="ajax-minicart-icon {if $minicart_total_items gt 0}full{else}empty{/if}" alt="" /></td>
</tr>
</table>
{include file="customer/minicart_total.tpl"}
</div>

{/capture}
{if $config.General.ajax_add2cart eq 'Y' and $main ne 'cart' and $main ne 'checkout' and $minicart_total_items gt 0}
  {assign var=additional_class value="menu-minicart ajax-minicart"}
{else}
  {assign var=additional_class value="menu-minicart"}
{/if}
{if $minicart_total_items gt 0}
  {assign var=additional_class value="`$additional_class` full-mini-cart"}
{/if}

<div class="{$additional_class}">
	{$smarty.capture.menu}
</div>
