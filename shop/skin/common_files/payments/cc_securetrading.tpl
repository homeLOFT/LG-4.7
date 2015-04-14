{*
92f1206cf48f45411b7d1db0a9e263848b1a618d, v2 (xcart_4_4_0), 2010-08-04 07:15:54, cc_securetrading.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
<h1>SECURETRADING.net</h1>
{$lng.txt_cc_configure_top_text}
<br /><br />
{$lng.txt_cc_securetrading_desc}
<br /><br />
{capture name=dialog}
<form action="cc_processing.php?cc_processor={$smarty.get.cc_processor|escape:"url"}" method="post">

<table cellspacing="10">
<tr>
<td>{$lng.lbl_cc_securetrading_login}:</td>
<td><input type="text" name="param01" size="32" value="{$module_data.param01|escape}" /></td>
</tr>
<tr>
<td>{$lng.lbl_cc_order_prefix}:</td>
<td><input type="text" name="param02" size="32" value="{$module_data.param02|escape}" /></td>
</tr>
<tr>
<td>{$lng.lbl_cc_securetrading_set_number}:</td>
<td><input type="text" name="param04" size="8" value="{$module_data.param04|escape}" /></td>
</tr>
<tr>
<td>{$lng.lbl_cc_currency}:</td>
<td>
<select name="param03">
<option value="GBP"{if $module_data.param03 eq "GBP"} selected="selected"{/if}>Britain Pound</option>
<option value="EUR"{if $module_data.param03 eq "EUR"} selected="selected"{/if}>Euro</option>
<option value="USD"{if $module_data.param03 eq "USD"} selected="selected"{/if}>US Dollar</option>
</select>
</td>
</tr>
</table>
<br /><br />
<input type="submit" value="{$lng.lbl_update|strip_tags:false|escape}" />
</form>

{/capture}
{include file="dialog.tpl" title=$lng.lbl_cc_settings content=$smarty.capture.dialog extra='width="100%"'}
