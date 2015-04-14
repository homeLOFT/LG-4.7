{*
7d575df087ddf61d08f0f6f4f535507c6a1d252d, v2 (xcart_4_5_1), 2012-06-25 07:18:55, cc_awallet.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
<h1>{$module_data.module_name}</h1>
{$lng.txt_cc_configure_top_text}
<br /><br />
{$lng.txt_cc_awallet_allowed_ips}
<br /><br />
{capture name=dialog}
<form action="cc_processing.php?cc_processor={$smarty.get.cc_processor|escape:"url"}" method="post">

<table cellspacing="10" width="100%">

<tr>
  <td width="40%">{$lng.lbl_cc_awallet_merchantid}:</td>
  <td width="60%"><input type="text" name="param01" size="32" value="{$module_data.param01|escape}" /></td>
</tr>

<tr>
  <td width="40%">{$lng.lbl_cc_awallet_siteid}:</td>
  <td width="60%"><input type="text" name="param02" size="32" value="{$module_data.param02|escape}" /></td>
</tr>

{include file="payments/currencies.tpl" param_name='param03' current=$module_data.param03}

<tr>
  <td>{$lng.lbl_cc_order_prefix}:</td>
  <td><input type="text" name="param04" size="32" value="{$module_data.param04|escape}" /></td>
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
