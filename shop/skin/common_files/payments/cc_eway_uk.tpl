{*
8193238dc64bf0bc1e388f8d27db186a22c05b37, v2 (xcart_4_4_4), 2011-08-04 09:45:06, cc_eway_uk.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
<h1>{$module_data.module_name}</h1>
{$lng.txt_cc_configure_top_text}
<br /><br />
{capture name=dialog}
<form action="cc_processing.php?cc_processor={$smarty.get.cc_processor|escape:"url"}" method="post">

<table cellspacing="10" width="100%">

<tr>
  <td width="40%">{$lng.lbl_cc_eway_customerid}:</td>
  <td width="60%"><input type="text" name="param01" size="32" value="{$module_data.param01|escape}" /></td>
</tr>

<tr>
  <td width="40%">{$lng.lbl_cc_eway_username}:</td>
  <td width="60%"><input type="text" name="param02" size="32" value="{$module_data.param02|escape}" /></td>
</tr>

<tr>
  <td>{$lng.lbl_cc_currency}:</td>
  <td>
    <select name="param03">
      {foreach from=$currencies item=c}
        <option value="{$c.code}"{if $module_data.param03 eq $c.code} selected="selected"{/if}>{$c.name} ({$c.code})</option>
      {/foreach}
    </select>
  </td>
</tr>

<tr>
  <td>{$lng.lbl_language}:</td>
  <td>
    <select name="param04">
      <option value="EN"{if $module_data.param04 eq "EN"} selected="selected"{/if}>English</option>
      <option value="ES"{if $module_data.param04 eq "ES"} selected="selected"{/if}>Spanish</option>
      <option value="FR"{if $module_data.param04 eq "FR"} selected="selected"{/if}>French</option>
      <option value="DE"{if $module_data.param04 eq "DE"} selected="selected"{/if}>German</option>
      <option value="NL"{if $module_data.param04 eq "NL"} selected="selected"{/if}>Dutch</option>
    </select>
  </td>
</tr>

<tr>
  <td>{$lng.lbl_cc_order_prefix}:</td>
  <td><input type="text" name="param05" size="32" value="{$module_data.param05|escape}" /></td>
</tr>

<tr>
  <td>{$lng.lbl_cc_eway_page_title}:</td>
  <td><input type="text" name="param06" size="32" value="{$module_data.param06|escape}" /></td>
</tr>

<tr>
  <td>{$lng.lbl_cc_eway_page_descr}:</td>
  <td><input type="text" name="param07" size="32" value="{$module_data.param07|escape}" /></td>
</tr>

<tr>
  <td>{$lng.lbl_cc_eway_page_footer}:</td>
  <td><input type="text" name="param08" size="32" value="{$module_data.param08|escape}" /></td>
</tr>

<tr>
  <td>{$lng.lbl_cc_eway_merchant_country}:</td>
  <td>
    <select name="param09" onchange="javascript:$('#eway_pm_script').text(this.value)">
      <option value="payment"{if $module_data.param09 eq "payment"} selected="selected"{/if}>United Kingdom (Great Britain)</option>
      <option value="au"{if $module_data.param09 eq "au"} selected="selected"{/if}>Australia</option>
      <option value="nz"{if $module_data.param09 eq "nz"} selected="selected"{/if}>New Zealand</option>
    </select>&nbsp;https://<span id='eway_pm_script'>{$module_data.param09|default:'payment'}</span>.ewaygateway.com/Request
  </td>
</tr>

</table>

<br /><br />

<input type="submit" value="{$lng.lbl_update|strip_tags:false|escape}" />
</form>

{/capture}
{include file="dialog.tpl" title=$lng.lbl_cc_settings content=$smarty.capture.dialog extra='width="100%"'}

<br /><br />
<b>{$lng.lbl_testmode_notes}</b><br />
<div class="cctest-description">{$lng.txt_test_descr_cc_eway_uk}</div>
