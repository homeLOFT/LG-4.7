{*
8faeb12f1c73aa112ed3404e046e0d7ce670780d, v6 (xcart_4_5_5), 2012-12-28 18:48:11, register_provider.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}

{capture name=dialog}

{$lng.txt_seller_address_note}

<br />
<br />

{assign var="reg_error" value=$top_message.reg_error}

{if $config.Shipping.allow_change_seller_address ne "Y" and $main ne "user_profile"}

<table cellspacing="1" cellpadding="2" width="100%">

<tr>
<td align="right" nowrap="nowrap" width="40%">{$lng.lbl_address}:</td>
<td nowrap="nowrap">
{$userinfo.seller_address|escape}
</td>
</tr>

<tr>
<td align="right">{$lng.lbl_address_2}:</td>
<td nowrap="nowrap">
{$userinfo.seller_address_2|escape}
</td>
</tr>

<tr>
<td align="right">{$lng.lbl_city}:</td>
<td nowrap="nowrap">
{$userinfo.seller_city|escape}
</td>
</tr>

<tr>
<td align="right">{$lng.lbl_state}:</td>
<td nowrap="nowrap">
{$userinfo.seller_statename|escape}
</td>
</tr>

<tr>
<td align="right">{$lng.lbl_country}:</td>
<td nowrap="nowrap">
{$userinfo.seller_countryname|escape}
</td>
</tr>

<tr>
<td align="right">{$lng.lbl_zip_code}:</td>
<td nowrap="nowrap">
{$userinfo.seller_zipcode|escape}
</td>
</tr>

{if $userinfo.need_arb_info eq "Y"}

<tr>
<td colspan="2" align="center">{include file="main/subheader.tpl" title=$lng.lbl_arb_provider_section}</td>
</tr>

<tr>
<td align="right">{$lng.opt_ARB_id}:</td>
<td nowrap="nowrap">
{$userinfo.seller_arb_id|escape}
</td>
</tr>

<tr>
<td align="right">{$lng.opt_ARB_password}:</td>
<td nowrap="nowrap">
{$userinfo.seller_arb_password|escape}
</td>
</tr>

<tr>
<td align="right">{$lng.opt_ARB_account}:</td>
<td nowrap="nowrap">
{$userinfo.seller_arb_account|escape}
</td>
</tr>

<tr>
<td align="right">{$lng.opt_ARB_shipping_key}:</td>
<td nowrap="nowrap">
{$userinfo.seller_arb_shipping_key|escape}
</td>
</tr>

<tr>
<td align="right">{$lng.opt_ARB_shipping_key_intl}:</td>
<td nowrap="nowrap">
{$userinfo.seller_arb_shipping_key_intl|escape}
</td>
</tr>

{/if}

</table>

{else}

<form action="{$register_script_name}?{$php_url.query_string|escape}" method="post" name="registerform" onsubmit="javascript: return checkRegFormFields(this);" >

{if $smarty.get.mode eq "update"}
<input type="hidden" name="mode" value="update" />
{/if}
<input type="hidden" name="submode" value="seller_address" />

<table cellspacing="1" cellpadding="2" width="100%">

<tr>
<td class="data-name"><label for="address">{$lng.lbl_address}</label></td>
<td{if $default_fields.address.required eq 'Y'} class="data-required"{/if}>{if $default_fields.address.required eq 'Y'}*{/if}</td>
<td>
<input type="text" id="address" name="address" size="32" maxlength="255" value="{$userinfo.seller_address|escape}" />
</td>
</tr>

<tr>
<td class="data-name"><label for="address_2">{$lng.lbl_address_2}</label></td>
<td{if $default_fields.address_2.required eq 'Y'} class="data-required"{/if}>{if $default_fields.address_2.required eq 'Y'}*{/if}</td>
<td>
<input type="text" id="address_2" name="address_2" size="32" maxlength="128" value="{$userinfo.seller_address_2|escape}" />
</td>
</tr>

<tr>
<td class="data-name"><label for="city">{$lng.lbl_city}</label></td>
<td{if $default_fields.city.required eq 'Y'} class="data-required"{/if}>{if $default_fields.city.required eq 'Y'}*{/if}</td>
<td>
<input type="text" id="city" name="city" size="32" maxlength="64" value="{$userinfo.seller_city|escape}" />
</td>
</tr>

<tr>
<td class="data-name"><label for="state">{$lng.lbl_state}</label></td>
<td{if $default_fields.state.required eq 'Y'} class="data-required"{/if}>{if $default_fields.state.required eq 'Y'}*{/if}</td>
<td>
{include file="main/states.tpl" states=$states name="state" default=$userinfo.seller_state|default:$config.General.default_state default_country=$userinfo.seller_country|default:$config.General.default_country country_name="country" id="state"}
</td>
</tr>

<tr>
<td class="data-name"><label for="country">{$lng.lbl_country}</label></td>
<td{if $default_fields.country.required eq 'Y'} class="data-required"{/if}>{if $default_fields.country.required eq 'Y'}*{/if}</td>
<td>
<select name="country" id="country" onchange="check_zip_code_field(this, $('#zipcode').get(0))">
{section name=country_idx loop=$countries}
<option value="{$countries[country_idx].country_code}"{if $userinfo.seller_country eq $countries[country_idx].country_code} selected="selected"{elseif $countries[country_idx].country_code eq $config.General.default_country and $userinfo.seller_country eq ""} selected="selected"{/if}>{$countries[country_idx].country|amp}</option>
{/section}
</select>
</td>
</tr>

<tr style="display: none;">
  <td{if $default_fields.state.required eq 'Y'} class="data-required"{/if}>
  {include file="main/register_states.tpl" state_name="state" country_name="country" county_name="county" state_value=$userinfo.seller_state|default:$config.General.default_state county_value=$userinfo.seller_county}
   </td>
</tr>

<tr>
<td class="data-name"><label for="zipcode">{$lng.lbl_zip_code}</label></td>
<td{if $default_fields.zipcode.required eq 'Y'} class="data-required"{/if}>{if $default_fields.zipcode.required eq 'Y'}*{/if}</td>
<td>
{include file="main/zipcode.tpl" name="zipcode" id="zipcode" val=$userinfo.seller_zipcode zip4=$userinfo.seller_zip4}
</td>
</tr>

<tr>
<td colspan="3">
{$lng.lbl_company_location_country_provider_note|substitute:"X":$config.Company.location_country_name}
{if $userinfo.seller_country ne $config.Company.location_country and $userinfo.seller_country ne ""}<br />
<font class="Star">
{$lng.lbl_company_location_country_provider_warning|substitute:"X":$config.Company.location_country_name}
</font>
{/if}
</td>
</tr>

{if $userinfo.need_arb_info eq "Y"}

<tr>
<td colspan="3" align="center">{include file="main/subheader.tpl" title=$lng.lbl_arb_provider_section}</td>
</tr>

<tr>
<td class="data-name">{$lng.opt_ARB_id}:</td>
<td>&nbsp;</td>
<td nowrap="nowrap">
<input type="text" name="arb_id" value="{$userinfo.seller_arb_id|escape}" />
</td>
</tr>

<tr>
<td class="data-name">{$lng.opt_ARB_password}:</td>
<td>&nbsp;</td>
<td nowrap="nowrap">
<input type="text" name="arb_password" value="{$userinfo.seller_arb_password|escape}" />
</td>
</tr>

<tr>
<td class="data-name">{$lng.opt_ARB_account}:</td>
<td>&nbsp;</td>
<td nowrap="nowrap">
<input type="text" name="arb_account" value="{$userinfo.seller_arb_account|escape}" />
</td>
</tr>

<tr>
<td class="data-name">{$lng.opt_ARB_shipping_key}:</td>
<td>&nbsp;</td>
<td nowrap="nowrap">
<input type="text" name="arb_shipping_key" value="{$userinfo.seller_arb_shipping_key|escape}" />
</td>
</tr>

<tr>
<td class="data-name">{$lng.opt_ARB_shipping_key_intl}:</td>
<td>&nbsp;</td>
<td nowrap="nowrap">
<input type="text" name="arb_shipping_key_intl" value="{$userinfo.seller_arb_shipping_key_intl|escape}" />
</td>
</tr>

<tr>
<td colspan="3" align="left"><b>{$lng.lbl_note}:</b> {$lng.lbl_arb_provider_note}</td>
</tr>

{/if}

<tr>
<td colspan="2">&nbsp;</td>
<td><br /><input type="submit" value=" {$lng.lbl_save} " /></td>
</tr>

</table>

</form>
{/if}
{/capture}
{include file="dialog.tpl" title=$lng.lbl_seller_address content=$smarty.capture.dialog extra='width="100%"'}
