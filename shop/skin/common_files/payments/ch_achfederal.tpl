{*
1c2b6ebe99b971f290d0d402b94e84559baa9311, v1 (xcart_4_5_2), 2012-07-13 15:28:01, ch_achfederal.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
<h1>ACH Federal</h1>
{$lng.txt_cc_configure_top_text}
<br /><br />
{capture name=dialog}
<form action="cc_processing.php?cc_processor={$smarty.get.cc_processor|escape:"url"}" method="post">

<table cellspacing="10">

<tr>
<td>Token (16-byte GUID):</td>
<td><input type="text" name="param01" size="100" value="{$module_data.param01|escape}" /></td>
</tr>

<tr>
<td>NACHA ID (10 digit numeric):</td>
<td><input type="text" name="param02" size="20" maxlength="20" value="{$module_data.param02|escape}" /></td>
</tr>

<tr>
<td>SEC code:</td>
<td>
<select name="param03">
<option value="SEC"{if $module_data.param03 eq "SEC"} selected="selected"{/if}>SEC</option>
<option value="CCD"{if $module_data.param03 eq "CCD"} selected="selected"{/if}>CCD</option>
<option value="PPD"{if $module_data.param03 eq "PPD"} selected="selected"{/if}>PPD</option>
<option value="TEL"{if $module_data.param03 eq "TEL"} selected="selected"{/if}>TEL</option>
<option value="WEB"{if $module_data.param03 eq "WEB"} selected="selected"{/if}>WEB</option>
</select>
</td>
</tr>

<tr>
<td>{$lng.lbl_cc_order_prefix}:</td>
<td><input type="text" name="param09" size="20" value="{$module_data.param09|escape}" /></td>
</tr>

<tr>
<td>{$lng.lbl_cc_testlive_mode}:</td>
<td>
<select name="testmode">
<option value="Y"{if $module_data.testmode eq "Y"} selected="selected"{/if}>{$lng.lbl_cc_testlive_test}</option>
<option value="N"{if $module_data.testmode eq "N"} selected="selected"{/if}>{$lng.lbl_cc_testlive_live}</option>
</select>
</td>
</tr>

</table>

<br /><br />
<input type="submit" value="{$lng.lbl_update|strip_tags:false|escape}" />

</form>
{/capture}
{include file="dialog.tpl" title=$lng.lbl_cc_settings content=$smarty.capture.dialog extra='width="100%"'}
