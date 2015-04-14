{*
92f1206cf48f45411b7d1db0a9e263848b1a618d, v2 (xcart_4_4_0), 2010-08-04 07:15:54, ps_nochex.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
<h1>Nochex</h1>
{$lng.txt_cc_configure_top_text}
<br /><br />
{capture name=dialog}
<form action="cc_processing.php?cc_processor={$smarty.get.cc_processor|escape:"url"}" method="post">

<table cellspacing="10">
<tr>
<td>{$lng.lbl_cc_nochex_id}:</td>
<td><input type="text" name="param01" size="24" value="{$module_data.param01|escape}" /></td>
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

<tr>
<td>{$lng.lbl_cc_order_prefix}:</td>
<td><input type="text" name="param04" size="36" value="{$module_data.param04|escape}" /></td>
</tr>
</table>
<br /><br />
<input type="submit" value="{$lng.lbl_update|strip_tags:false|escape}" />
</form>

{/capture}
{include file="dialog.tpl" title=$lng.lbl_cc_settings content=$smarty.capture.dialog extra='width="100%"'}
