{*
10d4766a297b130dea8de7e1d6cd01925e213749, v2 (xcart_4_6_0), 2013-04-09 11:07:38, popup_unavailable_shipping.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
{config_load file="$skin_config"}
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title></title>
  {include file="service_css.tpl"}
</head>
<body{$reading_direction_tag}>
<table cellpadding="10" cellspacing="0" width="100%">
<tr>
  <td>

{if $config.Shipping.enable_shipping ne "Y"}

{if $usertype eq "A" or ($usertype eq "P" and $active_modules.Simple_mode)}
{$lng.txt_shipping_disabled_admin|substitute:"path":$catalogs.admin}
{else}
{$lng.txt_shipping_disabled_provider}
{/if}

<br />

{else}

{capture name=dialog}

{if $defined_shippings or $realtime_shippings}

{if $defined_shippings}
<b>{$lng.lbl_defined_shipping_methods}:</b><br />
{foreach from=$defined_shippings item=shipping}
{$shipping.shipping|trademark}<br />
{/foreach}
<br /><br />
{/if}

{if $realtime_shippings}
<b>{$lng.lbl_realtime_shipping_carriers}:</b><br />
{foreach from=$realtime_shippings item=shipping}
{$shipping.code|trademark}<br />
{/foreach}
{/if}

{else}

{$lng.lbl_all_shippings_available}

{/if}

{/capture}

<div align="center">{include file="dialog.tpl" content=$smarty.capture.dialog title=$lng.lbl_unavailable_shippings extra='width="100%"'}</div>

{/if}

<p align="right"><a href="javascript:window.close();"><b>{$lng.lbl_close_window}</b></a></p>
  </td>
</tr>
</table>
</body>
</html>

