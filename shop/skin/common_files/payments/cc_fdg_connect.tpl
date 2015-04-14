{*
bb2f9ac9f79dd68c294319e40c7bbbb6b64296c6, v11 (xcart_4_6_0), 2013-04-29 13:14:17, cc_fdg_connect.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
<h1>{$module_data.module_name}</h1>
{$lng.txt_cc_configure_top_text}
<br /><br />
{capture name=dialog}

<script type="text/javascript" language="JavaScript 1.2">
//<![CDATA[
var selected_currency = '{$module_data.param02}';

function change_region(region, ss_value) {ldelim}

  if (region != 'EMEA') {ldelim}
    $('#currency option[value!=840]').hide().prop('disabled', true);
    $('#timezone option[class=EMEA]').hide().prop('disabled', true);
    $('#timezone option[class=NA]').show().prop('disabled', false);
  {rdelim} else {ldelim}
    $('#currency option').show().prop('disabled', false);
    $('#timezone option[class=EMEA]').show().prop('disabled', false);
    $('#timezone option[class=NA]').hide().prop('disabled', true);
  {rdelim}
  if ($('#timezone option:selected').prop('disabled')) {ldelim}
    $('#timezone').val('GMT');
  {rdelim}
  if ($('#currency option:selected').prop('disabled')) {ldelim}
    $('#currency').val('840');
  {rdelim}

{rdelim}
//]]>
</script>

<form action="cc_processing.php?cc_processor={$smarty.get.cc_processor|escape:"url"}" method="post">

<table cellspacing="5" cellpadding="5">

<tr>
  <td>{$lng.lbl_cc_fdg_connect_region}:</td>
  <td>
    <select name="param01" onchange="javascript: change_region(this.value);">
      <option{if $module_data.param01 eq "NA"} selected="selected"{/if} value="NA">NA - North America</option>
      <option{if $module_data.param01 eq "EMEA"} selected="selected"{/if} value="EMEA">EMEA - Europe, the Middle East and Africa</option>
    </select>
  </td>
</tr>

<tr>
  <td>{$lng.lbl_cc_currency}:</td>
  <td>
    <select name="param02" id="currency">
      <option value="978"{if $module_data.param02 eq "978"} selected="selected"{/if}>Euro (EUR)</option>
      <option value="826"{if $module_data.param02 eq "826"} selected="selected"{/if}>Pounds Sterling (GBP)</option>
      <option value="840"{if $module_data.param02 eq "840"} selected="selected"{/if}>US Dollar (USD)</option>
      <option value="756"{if $module_data.param02 eq "756"} selected="selected"{/if}>Swiss Francs (CHF)</option>
      <option value="203"{if $module_data.param02 eq "203"} selected="selected"{/if}>Czech Koruna (CZK)</option>
      <option value="206"{if $module_data.param02 eq "206"} selected="selected"{/if}>Danish Krone (DKK)</option>
      <option value="392"{if $module_data.param02 eq "392"} selected="selected"{/if}>Japanese Yen (JPY)</option>
      <option value="710"{if $module_data.param02 eq "710"} selected="selected"{/if}>South African Rand (ZAR)</option>
      <option value="752"{if $module_data.param02 eq "752"} selected="selected"{/if}>Swedish Krona (SEK)</option>
      <option value="124"{if $module_data.param02 eq "124"} selected="selected"{/if}>Canadian Dollar (CAD)</option>
    </select>
  </td>
</tr>

<tr>
  <td>{$lng.lbl_cc_fdg_connect_storeid}:</td>
  <td>
    <input type="text" name="param03" size="32" value="{$module_data.param03|escape}" />
  </td>
</tr>

<tr>
  <td>{$lng.lbl_cc_fdg_secret_key}:</td>
  <td>
    <input type="password" name="param06" id="shared_secret" size="32" value="{$module_data.param06|escape}" />
  </td>
</tr>

<tr>
  <td>{$lng.lbl_cc_fdg_timezone}:</td>
  <td>
    <select name="param07" id="timezone">
      <option{if $module_data.param07 eq "GMT"} selected="selected"{/if} value="GMT">GMT</option>
      <option{if $module_data.param07 eq "CET"} selected="selected"{/if} value="CET" class="EMEA">CET</option>
      <option{if $module_data.param07 eq "EET"} selected="selected"{/if} value="EET" class="EMEA">EET</option>
      <option{if $module_data.param07 eq "EST"} selected="selected"{/if} value="EST" class="NA">EST</option>
      <option{if $module_data.param07 eq "CST"} selected="selected"{/if} value="CST" class="NA">CST</option>
      <option{if $module_data.param07 eq "MST"} selected="selected"{/if} value="MST" class="NA">MST</option>
      <option{if $module_data.param07 eq "PST"} selected="selected"{/if} value="PST" class="NA">PST</option>
    </select>
  </td>
</tr>

<tr>
  <td>{$lng.lbl_cc_fdg_connect_mode}:</td>
  <td>
    <select name="param04">
      <option{if $module_data.param04 eq "fullpay"} selected="selected"{/if} value="fullpay">FullPay</option>
      <option{if $module_data.param04 eq "payonly"} selected="selected"{/if} value="payonly">PayOnly</option>
      <option{if $module_data.param04 eq "payplus"} selected="selected"{/if} value="payplus">PayPlus</option>
    </select>
  </td>
</tr>

<tr>
  <td>{$lng.lbl_cc_3dsecure}:</td>
  <td>
    <select name="param08" id="secure">
      <option value="Y"{if $module_data.param08 eq "Y"} selected="selected"{/if}>{$lng.lbl_enabled}</option>
      <option value="N"{if $module_data.param08 eq "N"} selected="selected"{/if}>{$lng.lbl_disabled}</option>
    </select>
  </td>
</tr>


<tr>
  <td>{$lng.lbl_cc_order_prefix}:</td>
  <td>
    <input type="text" name="param05" size="32" value="{$module_data.param05|escape}" />
  </td>
</tr>

<tr>
  <td>{$lng.lbl_use_preauth_method}:</td>
  <td>
    <select name="use_preauth">
      <option value="">{$lng.lbl_auth_and_capture_method}</option>
      <option value="Y"{if $module_data.use_preauth eq "Y"} selected="selected"{/if}>{$lng.lbl_auth_method}</option>
    </select>
  </td>
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

<script type="text/javascript" language="JavaScript 1.2">
//<![CDATA[
change_region('{$module_data.param01}', '{$module_data.param06}');
//]]>
</script>

{/capture}
{include file="dialog.tpl" title=$lng.lbl_cc_settings content=$smarty.capture.dialog extra='width="100%"'}
