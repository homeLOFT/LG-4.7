{*
8aa48be3738200137a0388bf014095a89b5a1a77, v3 (xcart_4_7_1), 2015-03-16 15:05:13, cc_egold.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
<h1>E-Gold</h1>
{$lng.txt_cc_configure_top_text}
<br /><br />
{capture name=dialog}
<form action="cc_processing.php?cc_processor={$smarty.get.cc_processor|escape:"url"}" method="post">

<table cellspacing="10">
<tr>
<td>{$lng.lbl_cc_egold_account}:</td>
<td><input type="text" name="param01" size="32" value="{$module_data.param01|escape}" /></td>
</tr>
<tr>
<td>{$lng.lbl_cc_egold_name}:</td>
<td><input type="text" name="param02" size="32" value="{$module_data.param02|escape}" /></td>
</tr>
<tr>
<td>{$lng.lbl_cc_currency}:</td>
<td>
<select name="param03">
<option value="1"{if $module_data.param03 eq "1"} selected="selected"{/if}>US Dollar (USD)</option>
<option value="2"{if $module_data.param03 eq "2"} selected="selected"{/if}>Canadian Dollar (CAD)</option>
<option value="33"{if $module_data.param03 eq "33"} selected="selected"{/if}>French Franc (FRF)</option>
<option value="41"{if $module_data.param03 eq "41"} selected="selected"{/if}>Swiss Francs (CHF)</option>
<option value="44"{if $module_data.param03 eq "44"} selected="selected"{/if}>Gt. Britain Pound (GPB)</option>
<option value="49"{if $module_data.param03 eq "49"} selected="selected"{/if}>Deutschemark (DEM)</option>
<option value="61"{if $module_data.param03 eq "61"} selected="selected"{/if}>Australian Dollar (AUD)</option>
<option value="81"{if $module_data.param03 eq "81"} selected="selected"{/if}>Japanese Yen (JPY)</option>
<option value="85"{if $module_data.param03 eq "85"} selected="selected"{/if}>Euro (EUR)</option>
<option value="88"{if $module_data.param03 eq "88"} selected="selected"{/if}>Greek Drachma (GRD)</option>
</select>
</td>
</tr>

<tr>
<td>{$lng.lbl_cc_order_prefix}:</td>
<td><input type="text" name="param04" size="32" value="{$module_data.param04|escape}" /></td>
</tr>
</table>
<br /><br />
<input type="submit" value="{$lng.lbl_update|strip_tags:false|escape}" />
</form>

{/capture}
{include file="dialog.tpl" title=$lng.lbl_cc_settings content=$smarty.capture.dialog extra='width="100%"'}
