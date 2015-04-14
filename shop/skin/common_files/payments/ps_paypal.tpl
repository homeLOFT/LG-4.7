{*
c4876f0dc3c1be77d432d7277a473c36b8ae058c, v6 (xcart_4_6_1), 2013-09-07 11:40:24, ps_paypal.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
<table class="paypal-method-settings">

  <tr class="first">
    <td class="setting-name">{$lng.lbl_cc_paypal_acc}:</td>
    <td><input type="text" name="{$conf_prefix}[param08]" size="24" value="{$module_data.param08|escape}" /></td>
  </tr>

  <tr>
    <td>{$lng.lbl_cc_paypal_for}:</td>
    <td><input type="text" name="{$conf_prefix}[param09]" size="24" value="{$module_data.param09|escape}" /></td>
  </tr>

  <tr>
    <td class="setting-name">{$lng.lbl_cc_currency}:</td>
    <td>
      <select name="{$conf_prefix}[param03]">
        <option value="USD"{if $module_data.param03 eq "USD"} selected="selected"{/if}>U.S. Dollars (USD)</option>
        <option value="CAD"{if $module_data.param03 eq "CAD"} selected="selected"{/if}>Canadian Dollars (CAD)</option>
        <option value="EUR"{if $module_data.param03 eq "EUR"} selected="selected"{/if}>Euro (EUR)</option>
        <option value="GBP"{if $module_data.param03 eq "GBP"} selected="selected"{/if}>Pounds Sterling (GBP)</option>
        <option value="AUD"{if $module_data.param03 eq "AUD"} selected="selected"{/if}>Australian Dollars (AUD)</option>
        <option value="CHF"{if $module_data.param03 eq "CHF"} selected="selected"{/if}>Swiss Franc (CHF)</option>
        <option value="JPY"{if $module_data.param03 eq "JPY"} selected="selected"{/if}>Yen (JPY)</option>
        <option value="NOK"{if $module_data.param03 eq "NOK"} selected="selected"{/if}>Norwegian Krone (NOK)</option>
        <option value="NZD"{if $module_data.param03 eq "NZD"} selected="selected"{/if}>New Zealand Dollar (NZD)</option>
        <option value="PLN"{if $module_data.param03 eq "PLN"} selected="selected"{/if}>Polish Zloty (PLN)</option>
        <option value="SEK"{if $module_data.param03 eq "SEK"} selected="selected"{/if}>Swedish Krona (SEK)</option>
        <option value="SGD"{if $module_data.param03 eq "SGD"} selected="selected"{/if}>Singapore Dollar (SGD)</option>
        <option value="HKD"{if $module_data.param03 eq "HKD"} selected="selected"{/if}>Hong Kong Dollar (HKD)</option>
        <option value="DKK"{if $module_data.param03 eq "DKK"} selected="selected"{/if}>Danish Krone (DKK)</option>
        <option value="HUF"{if $module_data.param03 eq "HUF"} selected="selected"{/if}>Hungarian Forint (HUF)</option>
        <option value="CZK"{if $module_data.param03 eq "CZK"} selected="selected"{/if}>Czech Koruna (CZK)</option>
        <option value="BRL"{if $module_data.param03 eq "BRL"} selected="selected"{/if}>Brazilian Real (BRL)</option>
        <option value="ILS"{if $module_data.param03 eq "ILS"} selected="selected"{/if}>Israeli New Sheqel (ILS)</option>
        <option value="MYR"{if $module_data.param03 eq "MYR"} selected="selected"{/if}>Malaysian Ringgit (MYR)</option>
        <option value="MXN"{if $module_data.param03 eq "MXN"} selected="selected"{/if}>Mexican Peso (MXN)</option>
        <option value="PHP"{if $module_data.param03 eq "PHP"} selected="selected"{/if}>Philippine Peso (PHP)</option>
        <option value="TWD"{if $module_data.param03 eq "TWD"} selected="selected"{/if}>Taiwan New Dollar (TWD)</option>
        <option value="THB"{if $module_data.param03 eq "THB"} selected="selected"{/if}>Thai Baht (THB)</option>
      </select>
    </td>
  </tr>

  <tr>
    <td class="setting-name">{$lng.lbl_cc_order_prefix}:</td>
    <td>
      <input type="text" name="{$conf_prefix}[param06]" size="36" value="{$module_data.param06|escape}" />
    </td>
  </tr>

    <tr class="comment">
      <td>&nbsp;</td>
      <td>{$lng.txt_order_prefix_descr}</td>
    </tr>

  <tr>
    <td class="setting-name">{$lng.lbl_cc_testlive_mode}:</td>
    <td>
      <select name="{$conf_prefix}[testmode]">
        <option value="Y"{if $module_data.testmode eq "Y"} selected="selected"{/if}>{$lng.lbl_cc_testlive_test}</option>
        <option value="N"{if $module_data.testmode eq "N"} selected="selected"{/if}>{$lng.lbl_cc_testlive_live}</option>
      </select>
    </td>
  </tr>

  <tr class="comment">
    <td>&nbsp;</td>
    <td>{$lng.txt_paypal_sandbox_note}</td>
  </tr>

  <tr>
    <td class="setting-name">{$lng.lbl_use_preauth_method}:</td>
    <td>
      <select name="{$conf_prefix}[use_preauth]">
        <option value="">{$lng.lbl_auth_and_capture_method}</option>
        <option value="Y"{if $module_data.use_preauth eq 'Y'} selected="selected"{/if}>{$lng.lbl_auth_method}</option>
      </select>
    </td>
  </tr>

  <tr>
    <td class="setting-name">{$lng.lbl_paypal_address_override}:</td>
    <td>
      <input type="checkbox" name="paypal_address_override" value="Y"{if $config.paypal_address_override eq "Y"} checked="checked"{/if} />
      {include file="main/tooltip_js.tpl" id="t_paypal_address_override" text=$lng.txt_paypal_address_override_tooltip width='450'}
    </td>
  </tr>

  <tr class="comment last">
    <td>&nbsp;</td>
    <td>{$lng.txt_paypal_address_override_note}</td>
  </tr>

  <tr class="optional header">
    <td colspan="2">{$lng.lbl_optional_settings}</td>
  </tr>

<tr class="optional first">
  <td colspan="2">
<strong>{$lng.lbl_paypal_api_access_creditentials}</strong>
<p>{$lng.txt_paypal_std_api_access_note}</p>
  </td>
</tr>


<tr class="optional">
<td class="setting-name">{$lng.lbl_paypal_api_access_username}:</td>
<td><input type="text" name="{$conf_prefix}[param01]" size="24" value="{$module_data.param01|escape}" /></td>
</tr>

<tr class="optional">
<td class="setting-name">{$lng.lbl_paypal_api_access_password}:</td>
<td><input type="password" name="{$conf_prefix}[param02]" size="24" value="{$module_data.param02|escape}" /></td>
</tr>

<tr class="optional">
<td class="setting-name" valign="top">{$lng.lbl_paypal_api_use_method}:</td>
<td>
<table>
<tr>
  <td><input type="radio" id="APIS" name="{$conf_prefix}[param07]" value="S"{if $module_data.param07 ne 'C'} checked="checked"{/if} /></td>
  <td><label for="APIS">{$lng.lbl_paypal_api_signature_type}</label></td>
</tr>
<tr>
  <td><input type="radio" id="APIC" name="{$conf_prefix}[param07]" value="C"{if $module_data.param07 eq 'C'} checked="checked"{/if} /></td>
  <td><label for="APIC">{$lng.lbl_paypal_api_certificate_type}</label></td>
</tr>

<tr class="optional">
<td class="setting-name">{$lng.lbl_paypal_api_certificate_file}:</td>
<td>
xcart_dir/payment/certs/<input type="text" name="{$conf_prefix}[param04]" size="24" value="{$module_data.param04|escape}" />
</td>
</tr>

<tr class="optional last">
<td class="setting-name">{$lng.lbl_paypal_api_access_signature}:</td>
<td><input type="text" name="{$conf_prefix}[param05]" size="32" value="{$module_data.param05|escape}" /></td>
</tr>

</table>
</td>
</tr>

</table>
