{*
cfebe2acd0457e59436f669e441801d4f5b0620c, v14 (xcart_4_6_2), 2014-01-04 09:39:18, ps_paypal_advanced.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}

<table class="paypal-method-settings">

  <tr class="info first">
    <td colspan="2">{$lng.txt_paypal_advanced_config_notice}</td>
  </tr>

  <tr class="info">
    <td colspan="2">{$lng.txt_paypal_enable_secure_token}</td>
  </tr>

  <tr>
    <td class="setting-name">{$lng.lbl_paypal_api_partner}:</td>
    <td><input type="text" name="{$conf_prefix}[param02]" size="24" value="{$module_data.param02|escape}" /></td>
  </tr>

  <tr class="comment">
    <td>&nbsp;</td>
    <td>
        {if $module_data.processor eq 'ps_paypal_payflowlink.php'}
          {$lng.txt_paypal_partner_payflowlink}
        {else}
          {$lng.txt_paypal_partner_advanced}
        {/if}
    </td>
  </tr>

  <tr>
    <td class="setting-name">{$lng.lbl_merchant_login}:</td>
    <td><input type="text" name="{$conf_prefix}[param01]" size="24" value="{$module_data.param01|escape}" /></td>
  </tr>

  <tr class="comment">
    <td>&nbsp;</td>
    <td>{$lng.txt_paypal_payflow_merchant}</td>
  </tr>

  <tr>
    <td class="setting-name">{$lng.lbl_paypal_api_user}:</td>
    <td><input type="text" name="{$conf_prefix}[param04]" size="24" value="{$module_data.param04|escape}" /></td>
  </tr>

  <tr class="comment">
    <td>&nbsp;</td>
    <td>{$lng.txt_paypal_payflow_user}</td>
  </tr>

  <tr>
    <td class="setting-name">{$lng.lbl_paypal_api_password}:</td>
    <td><input type="password" name="{$conf_prefix}[param05]" size="24" value="{$module_data.param05|escape}" /></td>
  </tr>

  <tr class="comment">
    <td>&nbsp;</td>
    <td>{$lng.txt_paypal_payflow_password}</td>
  </tr>

  <tr>
    <td class="setting-name">{$lng.lbl_cc_currency}:</td>
    <td>
      <select name="{$conf_prefix}[param03]">
        <option value="USD"{if $module_data.param03 eq "USD"} selected="selected"{/if}>US Dollar (United States)</option>
        <option value="EUR"{if $module_data.param03 eq "EUR"} selected="selected"{/if}>Euro (Europe)</option>
        <option value="GBP"{if $module_data.param03 eq "GBP"} selected="selected"{/if}>Pound Sterling (United Kingdom)</option>
        <option value="CAD"{if $module_data.param03 eq "CAD"} selected="selected"{/if}>Canadian Dollar (Canada)</option>
        <option value="JPY"{if $module_data.param03 eq "JPY"} selected="selected"{/if}>Yen (Japan)</option>
        <option value="AUD"{if $module_data.param03 eq "AUD"} selected="selected"{/if}>Australian Dollar (Australia)</option>
      </select>
    </td>
  </tr>

  {if $module_data.processor eq 'ps_paypal_payflowlink.php'}
  <tr class="comment">
    <td>&nbsp;</td>
    <td>{$lng.lbl_paypal_use_express_method}</td>
  </tr>
  {/if}

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
    <td>{$lng.lbl_paypal_test_mode_note}</td>
  </tr>

  <tr class="last">
    <td class="setting-name">{$lng.lbl_use_preauth_method}:</td>
    <td>
      <select name="{$conf_prefix}[use_preauth]">
        <option value="">{$lng.lbl_auth_and_capture_method}</option>
        <option value="Y"{if $module_data.use_preauth eq 'Y'} selected="selected"{/if}>{$lng.lbl_auth_method}</option>
      </select>
    </td>
  </tr>

  <tr class="optional header">
    <td colspan="2">{$lng.lbl_optional_settings}</td>
  </tr>

  <tr class="optional first">
    <td class="setting-name">{$lng.lbl_cc_order_prefix}:</td>
    <td>
      <input type="text" name="{$conf_prefix}[param06]" size="36" value="{$module_data.param06|escape}" />
    </td>
  </tr>

  <tr class="optional comment">
    <td>&nbsp;</td>
    <td>{$lng.txt_order_prefix_descr}</td>
  </tr>

  <tr class="optional">
    <td class="setting-name">{$lng.txt_paypal_payflowcolor}:</td>
    <td>
      <input type="text" name="{$conf_prefix}[params][payflowcolor]" size="24" maxlength="30" value="{$module_data.params.payflowcolor|escape}" />
    </td>
  </tr>

  <tr class="optional comment">
    <td>&nbsp;</td>
    <td>{$lng.lbl_paypal_api_rgb_color}</td>
  </tr>

  <tr class="optional">
    <td class="setting-name">{$lng.txt_paypal_hdrimage}:</td>
    <td>
      <input type="text" name="{$conf_prefix}[params][hdrimg]" size="24" maxlength="127" value="{$module_data.params.hdrimg|escape}" />
    </td>
  </tr>

  <tr class="optional comment">
    <td>&nbsp;</td>
    <td>{$lng.txt_paypal_hdrimage_descr}</td>
  </tr>

  <tr class="optional">
    <td class="setting-name">{$lng.lbl_paypal_api_page_collapse_bgcolor}:</td>
    <td>
      <input type="text" name="{$conf_prefix}[params][pagecollapsebgcolor]" size="8" value="{$module_data.params.pagecollapsebgcolor}" />
    </td>
  </tr>

  <tr class="optional comment">
    <td>&nbsp;</td>
    <td>{$lng.lbl_paypal_api_rgb_color}</td>
  </tr>

  <tr class="optional">
    <td class="setting-name">{$lng.lbl_paypal_api_page_collapse_text_color}:</td>
    <td>
      <input type="text" name="{$conf_prefix}[params][pagecollapsetextcolor]" size="8" value="{$module_data.params.pagecollapsetextcolor|escape}" />
    </td>
  </tr>

  <tr class="optional comment">
    <td>&nbsp;</td>
    <td>{$lng.lbl_paypal_api_rgb_color}</td>
  </tr>

  <tr class="optional">
    <td class="setting-name">{$lng.lbl_paypal_api_page_button_bgcolor}:</td>
    <td>
      <input type="text" name="{$conf_prefix}[params][pagebuttonbgcolor]" size="8" value="{$module_data.params.pagebuttonbgcolor|escape}" />
    </td>
  </tr>

  <tr class="optional comment">
    <td>&nbsp;</td>
    <td>{$lng.lbl_paypal_api_rgb_color}</td>
  </tr>

  <tr class="optional">
    <td class="setting-name">{$lng.lbl_paypal_api_page_button_textcolor}:</td>
    <td>
      <input type="text" name="{$conf_prefix}[params][pagebuttontextcolor]" size="8" value="{$module_data.params.pagebuttontextcolor|escape}" />
    </td>
  </tr>

  <tr class="optional comment">
    <td>&nbsp;</td>
    <td>{$lng.lbl_paypal_api_rgb_color}</td>
  </tr>

  {* Note: This parameter is not yet functional and is scheduled by PayPal to be included later
  <tr class="optional">
    <td class="setting-name">{$lng.lbl_paypal_api_button_text}:</td>
    <td>
      <input type="text" name="{$conf_prefix}[params][buttontext]" size="24" value="{$module_data.params.buttontext|escape}" />
    </td>
  </tr>
  *}

  <tr class="optional">
    <td class="setting-name">{$lng.txt_paypal_uk_page_style}:</td>
    <td>
      <input type="text" name="{$conf_prefix}[params][pagestyle]" size="24" maxlength="30" value="{$module_data.params.pagestyle}" />
    </td>
  </tr>

  <tr class="optional comment last">
    <td>&nbsp;</td>
    <td>
      {if $module_data.processor eq 'ps_paypal_payflowlink.php'}
        {assign var='payflowpostfix' value='PayFlow'}
      {/if}
      {$lng.txt_paypal_pagestyle|substitute:"onclick":"javascript: $('#pageStyleInstructions`$payflowpostfix`').toggle();"}
    </td>
  </tr>

  <tr class="optional first last" id="pageStyleInstructions{$payflowpostfix}" style="display: none;">
    <td>&nbsp;</td>
    <td>{$lng.txt_paypal_pagestyle_instructions}</td>
  </tr>
</table>
