{*
f02bb7a6ce33ce6c154ac2551b29a5d0210c311b, v2 (xcart_4_5_5), 2013-01-18 14:08:35, ps_paypal_pro_hosted.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
<table class="paypal-method-settings">

  <tr class="first">
    <td class="setting-name">{$lng.lbl_paypal_api_access_username}:</td>
    <td><input type="text" name="{$conf_prefix}[params][api_username]" size="42" value="{$module_data.params.api_username|escape}" /></td>
  </tr>

  <tr>
    <td class="setting-name">{$lng.lbl_paypal_api_access_password}:</td>
    <td><input type="password" name="{$conf_prefix}[params][api_password]" size="42" value="{$module_data.params.api_password|escape}" /></td>
  </tr>

  <tr>
    <td valign="top">{$lng.lbl_paypal_api_use_method}:</td>
    <td>
      <table>
        <tr>
          <td><input type="radio" id="APISP" name="{$conf_prefix}[params][api_method]" value="S"{if $module_data.params.api_method ne 'C'} checked="checked"{/if} /></td>
          <td><label for="APISP">{$lng.lbl_paypal_api_signature_type}</label></td>
        </tr>
        <tr>
          <td><input type="radio" id="APICP" name="{$conf_prefix}[params][api_method]" value="C"{if $module_data.params.api_method eq 'C'} checked="checked"{/if} /></td>
          <td><label for="APICP">{$lng.lbl_paypal_api_certificate_type}</label></td>
        </tr>
      </table>
    </td>
  </tr>

  <tr>
    <td class="setting-name">{$lng.lbl_paypal_api_certificate_file}:</td>
    <td>
      xcart_dir/payment/certs/<input type="text" name="{$conf_prefix}[params][api_certificate]" size="42" value="{$module_data.params.api_certificate|escape}" />
    </td>
  </tr>

  <tr>
    <td class="setting-name">{$lng.lbl_paypal_api_access_signature}:</td>
    <td><input type="text" name="{$conf_prefix}[params][api_signature]" size="70" value="{$module_data.params.api_signature|escape}" /></td>
  </tr>

  <tr>
    <td class="setting-name">{$lng.lbl_cc_currency}:</td>
    <td>
      <select name="{$conf_prefix}[params][currency]">
        <option value="AUD"{if $module_data.params.currency eq 'AUD'} selected="selected"{/if}>Australian Dollar</option>
        <option value="BRL"{if $module_data.params.currency eq 'BRL'} selected="selected"{/if}>Brazilian Real</option>
        <option value="CAD"{if $module_data.params.currency eq 'CAD'} selected="selected"{/if}>Canadian Dollar</option>
        <option value="CHF"{if $module_data.params.currency eq 'CHF'} selected="selected"{/if}>Swiss Franc</option>
        <option value="CZK"{if $module_data.params.currency eq 'CZK'} selected="selected"{/if}>Czech Koruna</option>
        <option value="DKK"{if $module_data.params.currency eq 'DKK'} selected="selected"{/if}>Danish Krone</option>
        <option value="EUR"{if $module_data.params.currency eq 'EUR'} selected="selected"{/if}>Euro</option>
        <option value="GBP"{if $module_data.params.currency eq 'GBP' or $module_data.params.currency eq ''} selected="selected"{/if}>Pound Sterling</option>
        <option value="HKD"{if $module_data.params.currency eq 'HKD'} selected="selected"{/if}>Hong Kong Dollar</option>
        <option value="HUF"{if $module_data.params.currency eq 'HUF'} selected="selected"{/if}>Hungarian Forint</option>
        <option value="ILS"{if $module_data.params.currency eq 'ILS'} selected="selected"{/if}>Israeli New Sheqel</option>
        <option value="JPY"{if $module_data.params.currency eq 'JPY'} selected="selected"{/if}>Japanese Yen</option>
        <option value="MYR"{if $module_data.params.currency eq 'MYR'} selected="selected"{/if}>Malaysian Ringgit</option>
        <option value="MXN"{if $module_data.params.currency eq 'MXN'} selected="selected"{/if}>Mexican Peso</option>
        <option value="NOK"{if $module_data.params.currency eq 'NOK'} selected="selected"{/if}>Norwegian Krone</option>
        <option value="NZD"{if $module_data.params.currency eq 'NZD'} selected="selected"{/if}>New Zealand Dollar</option>
        <option value="PHP"{if $module_data.params.currency eq 'PHP'} selected="selected"{/if}>Philippine Peso</option>
        <option value="PLN"{if $module_data.params.currency eq 'PLN'} selected="selected"{/if}>Polish Zloty</option>
        <option value="SEK"{if $module_data.params.currency eq 'SEK'} selected="selected"{/if}>Swedish Krona</option>
        <option value="SGD"{if $module_data.params.currency eq 'SGD'} selected="selected"{/if}>Singapore Dollar</option>
        <option value="TWD"{if $module_data.params.currency eq 'TWD'} selected="selected"{/if}>Taiwan New Dollar</option>
        <option value="THB"{if $module_data.params.currency eq 'THB'} selected="selected"{/if}>Thai Baht</option>
        <option value="USD"{if $module_data.params.currency eq 'USD'} selected="selected"{/if}>U.S. Dollar</option>
      </select>
    </td>
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
      <input type="text" name="{$conf_prefix}[params][order_prefix]" size="36" value="{$module_data.params.order_prefix|escape}" />
    </td>
  </tr>

  <tr class="optional comment">
    <td>&nbsp;</td>
    <td>{$lng.txt_order_prefix_descr}</td>
  </tr>

  <tr class="optional">
    <td class="setting-name">{$lng.lbl_paypal_payflow_vendor}:</td>
    <td>
      <input type="text" name="{$conf_prefix}[params][payflow_vendor]" size="42" value="{$module_data.params.payflow_vendor|escape}" />
    </td>
  </tr>

  <tr class="optional">
    <td class="setting-name">{$lng.lbl_paypal_payflow_partner}:</td>
    <td>
      <input type="text" name="{$conf_prefix}[params][payflow_partner]" size="42" value="{$module_data.params.payflow_partner|escape}" />
    </td>
  </tr>

  <tr class="optional">
    <td class="setting-name">{$lng.txt_paypal_uk_payflowcolor}:</td>
    <td>
      <input type="text" name="{$conf_prefix}[params][payflowcolor]" size="24" maxlength="30" value="{$module_data.params.payflowcolor|escape}" />
    </td>
  </tr>

  <tr class="optional comment">
    <td>&nbsp;</td>
    <td>{$lng.lbl_paypal_api_rgb_color}</td>
  </tr>

  <tr class="optional">
    <td class="setting-name">{$lng.txt_paypal_uk_header_image_url}:</td>
    <td>
      <input type="text" name="{$conf_prefix}[params][hdrimg]" size="24" maxlength="127" value="{$module_data.params.hdrimg|escape}" />
    </td>
  </tr>

  <tr class="optional comment last">
    <td>&nbsp;</td>
    <td>{$lng.txt_paypal_hdrimage_descr}</td>
  </tr>

</table>
