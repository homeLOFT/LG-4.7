{*
4cf872650c331fbccd85b315692f96ff27d39ed0, v39 (xcart_4_7_0), 2014-12-30 17:01:14, configuration.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{if $htaccess_warning.$option eq 'Y'}

{capture name=dialog}

{$lng.lbl_htaccess_warning|substitute:"htaccess":$htaccess_path}

{/capture}

{include file="location.tpl" location="" alt_content=$smarty.capture.dialog extra='width="100%"' newid="htaccess_warning" alt_type="W"}

{/if}

{if $skip_config_page_title eq ""}

{include file="page_title.tpl" title=$option_title}

{/if}

{capture name=dialog}

{assign var="cycle_name" value="sep"}

{if $option ne "User_Profiles" and $option ne "Contact_Us" and $option ne "Search_products" and $custom_admin_module_config_tpl_file eq "" and $skip_config_page_form eq ""}
  <form action="configuration.php?option={$option|escape}" method="post" name="processform" onsubmit="return validateFields()">
{/if}

{if $option eq 'Shipping_Label_Generator'}
<table cellpadding="3" cellspacing="1" width="100%">
<tr>
  <td>
    <div align="right">
{capture name="button_href"}
javascript: popupOpen('popup_info.php?action=TSTLBL','{$lng.lbl_usps_labels_help}', {ldelim} width: 600, height: 460 {rdelim});
{/capture}
      {include file="buttons/button.tpl" button_title=$lng.lbl_usps_labels_help href=$smarty.capture.button_href}
    </div>
  </td>
</tr>
</table>
{/if}

{if $option eq "User_Profiles"}

  {include file="admin/main/user_profiles.tpl"}

{elseif $option eq "Contact_Us"}

  {include file="admin/main/contact_us_profiles.tpl"}

{elseif $custom_admin_module_config_tpl_file ne ""}

  {include file=$custom_admin_module_config_tpl_file}

{elseif $option eq "Search_products"}

  {include file="admin/main/search_products_form.tpl"}

{elseif $option eq "AfterShip"}

  {include file="modules/AfterShip/admin/configuration.tpl"}

{else}

{if $skip_config_page_validation eq ""}

{include file="admin/main/conf_fields_validation_js.tpl"}

{/if}

{if $option eq "Amazon_Checkout"}
  {getvar var='amazon_merchant_URL'}
  <div align="right">
    {include file="buttons/button.tpl" button_title=$lng.lbl_signup_for_acheckout href="https://payments.amazon.com/signup" target="_blank"}<br />
  </div>
  {$lng.txt_acheckout_setup_note|substitute:"callback_url":$amazon_merchant_URL}<br />
  <br />
{/if}

{if $option eq "Image_Verification"}
  {include file="modules/Image_Verification/spambot_requirements.tpl"}
{/if}

{if $option eq "Google_Analytics"}
  {$lng.google_analytics_info}<br />
  <br />
{/if}

{if $option eq "SEO"}
  {$lng.txt_clean_url_htaccess_info|substitute:"clean_url_htaccess":$clean_url_htaccess|substitute:"htaccess":$clean_url_htaccess_path|substitute:"clean_url_test_url":$clean_url_test_url}<br />
{/if}

{if $option eq "TaxCloud"}
  {include file="modules/TaxCloud/config.tpl"}
{/if}

{if $option eq "AvaTax"}
  {include file="modules/AvaTax/config.tpl"}
{/if}

{if $option eq 'PayPal_Login'}
  {$lng.txt_paypal_login_info|substitute:'paypal_login_return_url':$paypal_login_return_url|substitute:'paypal_login_privacy_url':$paypal_login_privacy_url}
{/if}

{if $option eq 'Bill_Me_Later'}
  {include file="modules/Bill_Me_Later/config.tpl"}
{/if}

<table cellpadding="0" cellspacing="0" class="general-settings">

{assign var="first_row" value=1}

{if $option eq "Appearance"}
  <tr>
    <td>
<script type="text/javascript">
//<![CDATA[
var previewShots = [];
{foreach from=$alt_skins_info item=alt_skin key=id}
previewShots['{$id}']='{$alt_skin.screenshot|escape:javascript}';
{/foreach}
var ssPreviewSrc = '{$alt_skin_info.screenshot|escape:javascript}';
//]]>
</script>
    </td>
    <td valign="top">
      <strong>{$lng.lbl_select_skin}</strong>
      <br /><br />
      <select name="alt_skin" id="alt_skin_id" onchange="javascript:$('#alt_image').attr('src', previewShots[this.value]);">
      {foreach from=$alt_skins_info item=alt_skin key=id}
      <option value="{$id}"{if $alt_skin eq $alt_skin_info} selected="selected"{/if}>{$alt_skin.name|escape}</option>
      {/foreach}
      </select>
      <br />
      <br />
      <a href="http://www.x-cart.com/xcart-templates.html?utm_source=xcart&amp;utm_medium=appearance_link&amp;utm_campaign=appearance" target='_blank' class='external-link more-xcart-templates-link'>{$lng.lbl_more_xcart_templates}</a>
<script type="text/javascript">
//<![CDATA[
$('#alt_image').attr('src', ssPreviewSrc);
//]]>
</script>
    </td>
    <td valign="top" id="alt_image_td">
      <img id="alt_image" src="{$alt_skin_info.screenshot|escape}" alt="" />
    </td>
  </tr>
{/if}

{section name=cat_num loop=$configuration}

{assign var="opt_comment" value="opt_`$configuration[cat_num].name`"}
{assign var="opt_label_id" value="opt_`$configuration[cat_num].name`"}
{assign var="opt_descr" value="opt_descr_`$configuration[cat_num].name`"}

{if $configuration[cat_num].type eq "separator"}

  <tr>
    <td colspan="3" class="TableSeparator">
      {if $lng.$opt_comment ne ""}
        {$lng.$opt_comment}
      {elseif $configuration[cat_num].comment}
        {$configuration[cat_num].comment}
      {else}
        <hr />
      {/if}
    </td>
  </tr>
  {assign var="cycle_name" value=$configuration[cat_num].name}

{else}

  {if $configuration[cat_num].pre_note}
    <tr>
      <td colspan="3">{$configuration[cat_num].pre_note}<br /><br /></td>
    </tr>
  {/if}

  {if $cols_count eq "1"}
    {assign var="bgcolor" value=""}
    {assign var="cols_count" value=""}
  {else}
    {assign var="bgcolor" value="class=''"}
    {assign var="cols_count" value="1"}
  {/if}

  {cycle name=$cycle_name values=" class='TableSubHead', " assign="row_style"}

  <tr id="tr_{$configuration[cat_num].name}" class="xcart-conf-option">
    <td width="30" {$row_style}>&nbsp;<a name="anchor_{$configuration[cat_num].name}"></a></td>
    <td {$row_style} width="60%">
      {strip}
        {if $configuration[cat_num].type eq "checkbox"}
          <label for="{$opt_label_id}">
        {/if}
        {$lng.$opt_comment|default:$configuration[cat_num].comment}
        {if $configuration[cat_num].type eq "checkbox"}
          </label>
        {/if}
      {/strip}
    </td>
    <td {$row_style} width="40%">

    <table cellpadding="0" cellspacing="0">
    <tr>
      <td>
      
      {if $configuration[cat_num].name eq "blowfish_enabled" and $configuration[cat_num].value eq "Y" and $is_merchant_password ne "Y"}

        {$lng.lbl_enabled}
        <input type="hidden" name="{$configuration[cat_num].name}" value='{$configuration[cat_num].value}' />

      {elseif $configuration[cat_num].name eq "periodic_logs"}

        <input type="hidden" name="periodic_logs" value="" />
        <select name="periodic_logs[]" multiple="multiple" size="10">
          {foreach key=log_label item=txt_label from=$periodical_logs_names}
            <option value="{$log_label}"{if $periodical_log_labels.$log_label ne ""} selected="selected"{/if}>{$txt_label}</option>
          {/foreach}
        </select>

      {elseif $configuration[cat_num].type eq "numeric"}

        <input id="{$configuration[cat_num].name}" type="text" size="10" name="{$configuration[cat_num].name}" value="{$configuration[cat_num].value|formatnumeric}" />

      {elseif $configuration[cat_num].type eq "text" or $configuration[cat_num].type eq "trimmed_text"}

        <input type="text" size="30" name="{$configuration[cat_num].name}" value="{$configuration[cat_num].value|escape:htmlall}" />
      
      {elseif $configuration[cat_num].type eq "password"}

        <input type="password" size="30" name="{$configuration[cat_num].name}" id="{$opt_label_id}" value="{$configuration[cat_num].value|escape:htmlall}" />

      {elseif $configuration[cat_num].type eq "checkbox"}

        {if $configuration[cat_num].disabled}
          <input type="hidden" name="{$configuration[cat_num].name}" value="{$configuration[cat_num].value|escape}" />
        {/if}
        <input type="checkbox" id="{$opt_label_id}" name="{$configuration[cat_num].name}"{if $configuration[cat_num].value eq "Y"} checked="checked"{/if}{if $configuration[cat_num].disabled} disabled="disabled"{/if} />

      {elseif $configuration[cat_num].type eq "textarea"}

        <textarea name="{$configuration[cat_num].name}" cols="30" rows="5">{$configuration[cat_num].value|escape:html}</textarea>

      {elseif $configuration[cat_num].type eq "selector" and $configuration[cat_num].variants ne ''}

        <select name="{$configuration[cat_num].name}"{if $configuration[cat_num].auto_submit} onchange="javascript: document.processform.submit()"{/if}>
          {foreach from=$configuration[cat_num].variants item=vitem key=vkey}
            <option value="{$vkey}"{if $vitem.selected} selected="selected"{/if}>{$vitem.name}</option>
          {/foreach}
        </select>

      {elseif $configuration[cat_num].type eq "multiselector" and $configuration[cat_num].variants ne ''}

        <select name="{$configuration[cat_num].name}[]" multiple="multiple" size="5">
          {foreach from=$configuration[cat_num].variants item=vitem key=vkey}
            <option value="{$vkey}"{if $vitem.selected} selected="selected"{/if}>{$vitem.name}</option>
          {/foreach}
        </select>

      {elseif $configuration[cat_num].type eq "state"}

        {include file="main/states.tpl" states=$states name=$configuration[cat_num].name default=$configuration[cat_num].value default_country=$configuration[cat_num].country_value}
        {* smarty {section} access, not array access! cat_num is the name of the section. Not an array index *}
        {$state_values[$configuration[cat_num].prefix]=$configuration[cat_num].value}

      {elseif $configuration[cat_num].type eq 'country'}

        <select name="{$configuration[cat_num].name}" id="{$configuration[cat_num].name}">
          {foreach from=$countries item=c}
            <option value="{$c.country_code}"{if $c.country_code eq $configuration[cat_num].value} selected="selected"{/if}>{$c.country}</option>
          {/foreach}
        </select>

      {/if}

      {if $configuration[cat_num].prefix}

        {assign var="prefix" value=$configuration[cat_num].prefix}
        {$dynamic_states[$prefix]=$dynamic_states[$prefix]+1}

      {/if}
      </td>
      <td valign="middle">
      {if $lng.$opt_descr}
        
        {include file="main/tooltip_js.tpl" title=$lng.$opt_comment|default:$configuration[cat_num].comment text=$lng.$opt_descr id="help_`$configuration[cat_num].name`" type="img" sticky=true}

      {else}
        &nbsp;
      {/if}
      </td>
    </tr>
    </table>
    </td>
  </tr>

  {capture name=additional_line}{strip}
    {if $configuration[cat_num].name eq 'speedup_css'}
      {$lng.txt_speedup_description|substitute:"speed_up_htaccess":$speed_up_htaccess|substitute:"htaccess_file":$htaccess_file}
    {elseif $configuration[cat_num].error}
      <span class="ErrorMessage">{$configuration[cat_num].error}</span>
    {elseif $configuration[cat_num].warning}
      <strong>{$lng.lbl_warning}:</strong> {$configuration[cat_num].warning}
    {elseif $configuration[cat_num].note}
      <strong>{$lng.lbl_note}:</strong> {$configuration[cat_num].note}
    {elseif $configuration[cat_num].raw_note}
      {$configuration[cat_num].raw_note}
    {/if}
  {/strip}{/capture}

  {if $smarty.capture.additional_line}
    <tr>
      <td width="30" {$row_style}>&nbsp;</td>
      <td colspan="2" {$row_style}>
        {$smarty.capture.additional_line}
      </td>
    </tr>
    <tr>
      <td colspan="3" {$row_style}></td>
    </tr>
  {/if}

{/if}

{assign var="first_row" value=0}

{/section}

{if $dynamic_states ne ''}
  <tr style="display: none;">
    <td>
      {include file="change_states_js.tpl"}
      {foreach from=$dynamic_states item=cnt key=name}
        {if $cnt eq 2}
          {include file="main/register_states.tpl" state_name="`$name`_state" country_name="`$name`_country" state_value=$state_values.$name}
        {/if}
      {/foreach}
    </td>
  </tr>
{/if}

{if $option eq "AvaTax"}
  <tr>
    <td colspan="3">
      {include file="modules/AvaTax/config_bottom.tpl"}
    </td>
  </tr>
{/if}

{if $option eq 'Bill_Me_Later'}
  <tr>
    <td colspan="3">
      {include file="modules/Bill_Me_Later/config_bottom.tpl"}
    </td>
  </tr>
{/if}

<tr>
  <td colspan="3">
    <br /><br />
    <div id="sticky_content">
      <div class="main-button">
        <input type="submit" class="big-main-button configure-style" value="{$lng.lbl_apply_changes|strip_tags:false|escape}" />
      </div>
    </div>
  </td>
</tr>

</table>

{if $option eq "Company"}
  {if $smarty.cookies.hide_company_adv eq ''}
    {load_defer file="admin/js/show_company_adv.js" type="js"}
  {/if}
  {if not $single_mode}
  <br />
    <strong>{$lng.lbl_note}:</strong> {$lng.lbl_company_location_country_note}
  {/if}    
{/if}

{if $option ne "User_Profiles" and $option ne "Contact_Us" and $option ne "Search_products" and $custom_admin_module_config_tpl_file eq "" and $skip_config_page_form eq ""}
  </form>
{/if}

{if $option eq "Shipping" and $is_realtime}

  <hr />

  <h3>{$lng.lbl_test_realtime_calculation}</h3>

  {$lng.txt_test_realtime_calculation_text}

  <br /><br />

  <form action="test_realtime_shipping.php" target="_blank">

    <label for="trs_weight">{$lng.lbl_package_weight}</label> <input type="text" id="trs_weight" name="weight" value="1" /> <input type="submit" value="{$lng.lbl_test|strip_tags:false|escape}" />

  </form>

{elseif $option eq "Security"}

  <hr />

  <h3>{$lng.lbl_test_data_encryption}</h3>

  <a href="test_pgp.php">{$lng.lbl_test_data_encryption_link}</a>

{/if}

<br />

{/if}

{if $additional_config}
  {include file=$additional_config}
{/if}

{/capture}
{include file="dialog.tpl" content=$smarty.capture.dialog extra='width="100%"'}
