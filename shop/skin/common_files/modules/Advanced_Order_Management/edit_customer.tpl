{*
efdb1978092cd94543a5472d62e7e28d1ad0aaa0, v13 (xcart_4_7_0), 2015-02-26 17:28:14, edit_customer.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{include file="change_states_js.tpl"}
{include file="check_zipcode_js.tpl"}

{capture name=dialog}
<form action="order.php" method="post" name="editcustomer_form">
<input type="hidden" name="mode" value="edit" />
<input type="hidden" name="action" value="update_customer" />
<input type="hidden" name="show" value="customer" />
<input type="hidden" name="orderid" value="{$orderid}" />
<input type="hidden" name="customer_info[tax_exempt]" value="N" />

{include file="main/subheader.tpl" title=$lng.lbl_customer_info}

<table cellspacing="1" cellpadding="3" width="100%">

<tr valign="top">
  <td colspan="3"><i>{$lng.lbl_personal_information}:</i></td>
</tr>

<tr class="TableHead">
  <td height="16">&nbsp;</td>
  <th align="left">{$lng.lbl_aom_current_value}</th>
  <th align="left">{$lng.lbl_aom_original_value}</th>
</tr>

<tr{cycle name=c1 values=', class="TableSubHead"'}>
    <td>{$lng.lbl_email}</td>
    <td><input type="text" name="customer_info[email]" size="32" maxlength="128" value="{$cart_customer.email|escape}" {include file="main/attr_orig_data.tpl" data_orig_value=$customer.email data_orig_keep_empty='Y'} /></td>
    <td><a href="mailto:{$customer.email}">{$customer.email}</a></td>
</tr>

{if $default_fields.title.avail eq 'Y' or $cart_customer.titleid ne ''}
<tr{cycle name=c1 values=', class="TableSubHead"'}{if $default_fields.title.avail ne 'Y'} data-aom-field-not-avail{/if}>
  <td>{$lng.lbl_title}</td>
  <td>
    {include file="main/title_selector.tpl" val=$cart_customer.titleid use_title_id="Y" name="customer_info[titleid]" id="titleid" data_orig_value=$customer.titleid data_orig_keep_empty='Y'}
  </td>
  <td>{$customer.title}</td>
</tr>
{/if}
{if $default_fields.firstname.avail eq 'Y' or $cart_customer.firstname ne ''}
<tr{cycle name=c1 values=', class="TableSubHead"'}{if $default_fields.firstname.avail ne 'Y'} data-aom-field-not-avail{/if}>
  <td>{$lng.lbl_first_name}</td>
  <td><input type="text" name="customer_info[firstname]" size="32" maxlength="128" value="{$cart_customer.firstname|escape}" {include file="main/attr_orig_data.tpl" data_orig_value=$customer.firstname data_orig_keep_empty='Y'} /></td>
  <td>{$customer.firstname}</td>
</tr>
{/if}
{if $default_fields.lastname.avail eq 'Y' or $cart_customer.lastname ne ''}
<tr{cycle name=c1 values=', class="TableSubHead"'}{if $default_fields.lastname.avail ne 'Y'} data-aom-field-not-avail{/if}>
  <td>{$lng.lbl_last_name}</td>
  <td><input type="text" name="customer_info[lastname]" size="32" maxlength="128" value="{$cart_customer.lastname|escape}" {include file="main/attr_orig_data.tpl" data_orig_value=$customer.lastname data_orig_keep_empty='Y'} /></td>
  <td>{$customer.lastname}</td>
</tr>
{/if}
{if $default_fields.company.avail eq 'Y' or $cart_customer.company ne ''}
<tr{cycle name=c1 values=', class="TableSubHead"'}{if $default_fields.company.avail ne 'Y'} data-aom-field-not-avail{/if}>
  <td>{$lng.lbl_company}</td>
  <td><input type="text" name="customer_info[company]" size="32" maxlength="255" value="{$cart_customer.company|escape}" {include file="main/attr_orig_data.tpl" data_orig_value=$customer.company data_orig_keep_empty='Y'} /></td>
  <td>{$customer.company}</td>
</tr>
{/if}
{if $default_fields.url.avail eq 'Y' or $cart_customer.url ne ''}
<tr{cycle name=c1 values=', class="TableSubHead"'}{if $default_fields.url.avail ne 'Y'} data-aom-field-not-avail{/if}>
  <td>{$lng.lbl_web_site}</td>
  <td><input type="text" name="customer_info[url]" size="32" maxlength="128" value="{$cart_customer.url|escape}" {include file="main/attr_orig_data.tpl" data_orig_value=$customer.url data_orig_keep_empty='Y'} /></td>
  <td>{if $customer.url}<a href="{$customer.url}">{$customer.url}</a>{else}&nbsp;{/if}</td>
</tr>
{/if}
{if $default_fields.ssn.avail eq 'Y' or $cart_customer.ssn ne ''}
<tr{cycle name=c1 values=', class="TableSubHead"'}{if $default_fields.ssn.avail ne 'Y'} data-aom-field-not-avail{/if}>
  <td>{$lng.lbl_ssn}</td>
  <td><input type="text" name="customer_info[ssn]" size="32" maxlength="32" value="{$cart_customer.ssn|escape}" {include file="main/attr_orig_data.tpl" data_orig_value=$customer.ssn data_orig_keep_empty='Y'} /></td>
  <td>{$customer.ssn}</td>
</tr>
{/if}
{if $default_fields.tax_number.avail eq 'Y' or $cart_customer.tax_number ne ''}
<tr{cycle name=c1 values=', class="TableSubHead"'}{if $default_fields.tax_number.avail ne 'Y'} data-aom-field-not-avail{/if}>
  <td>{$lng.lbl_tax_number}</td>
  <td><input type="text" name="customer_info[tax_number]" size="32" maxlength="32" value="{$cart_customer.tax_number|escape}" {include file="main/attr_orig_data.tpl" data_orig_value=$customer.tax_number data_orig_keep_empty='Y'} /></td>
  <td>{$customer.tax_number}</td>
</tr>
{/if}
{if $config.Taxes.tax_operation_scheme eq "TAX_SCHEME_GENERAL"} {* XCTaxesDefs::TAX_SCHEME_GENERAL *}
<tr{cycle name=c1 values=', class="TableSubHead"'}>
  <td>{$lng.lbl_tax_exemption}</td>
  <td><input type="checkbox" name="customer_info[tax_exempt]" value="Y"{if $cart_customer.tax_exempt eq "Y"} checked="checked"{/if} {include file="main/attr_orig_data.tpl" data_orig_value=($customer.tax_exempt eq 'Y') data_orig_keep_empty='Y'} /></td>
  <td>{if $customer.tax_exempt eq "Y"}{$lng.txt_tax_exemption_assigned}{else}{$lng.txt_not_available}{/if}</td>
</tr>
{/if}
{if $membership_levels}
<tr{cycle name=c1 values=', class="TableSubHead"'}>
  <td>{$lng.lbl_membership}</td>
  <td>
  <select name="customer_info[membershipid]" {include file="main/attr_orig_data.tpl" data_orig_value=$customer.membershipid data_orig_keep_empty='Y'}>
    <option value="0">{$lng.lbl_not_member}</option> 
  {foreach from=$membership_levels item=m key=mid}
    <option value="{$mid}"{if $cart_customer.membershipid eq $mid} selected="selected"{/if}>{$m.membership}</option>
  {/foreach}
  </select>
  </td>
  <td>{$customer.membership|default:$lng.lbl_not_member}</td>
</tr>
{/if}

{include file="modules/Advanced_Order_Management/edit_additional_fields.tpl" additional_fields=$cart_customer.additional_fields cycle_name='c1' fields_section='P' data_orig_keep_empty='Y'}

<tr>
  <td colspan="3">&nbsp;</td>
</tr>

<tr valign="top">
  <td colspan="3"><i>{$lng.lbl_billing_address}:</i></td>
</tr>
<tr class="TableHead">
  <td height="16">&nbsp;</td>
  <th height="16" align="left">{$lng.lbl_aom_current_value}</th>
  <th height="16" align="left">{$lng.lbl_aom_original_value}</th>
</tr>
{if $address_fields.title.avail eq 'Y' or $customer.b_title ne ''}
<tr{cycle name=c2 values=', class="TableSubHead"'}{if $address_fields.title.avail ne 'Y'} data-aom-field-not-avail{/if}>
  <td>{$lng.lbl_title}</td>
  <td>
    {include file="main/title_selector.tpl" name="address_book[B][title]" id="b_title" val=$cart_customer.b_title data_orig_value=$customer.b_title data_orig_keep_empty='Y'}
  </td>
  <td>{$customer.b_title}</td>
</tr>
{/if}
{if $address_fields.firstname.avail eq 'Y' or $customer.b_firstname ne ''}
<tr{cycle name=c2 values=', class="TableSubHead"'}{if $address_fields.firstname.avail ne 'Y'} data-aom-field-not-avail{/if}>
  <td>{$lng.lbl_first_name}</td>
  <td><input type="text" name="address_book[B][firstname]" size="32" maxlength="128" value="{$cart_customer.b_firstname|escape}" {include file="main/attr_orig_data.tpl" data_orig_value=$customer.b_firstname data_orig_keep_empty='Y'} /></td>
  <td>{$customer.b_firstname}</td>
</tr>
{/if}
{if $address_fields.lastname.avail eq 'Y' or $customer.b_lastname ne ''}
<tr{cycle name=c2 values=', class="TableSubHead"'}{if $address_fields.lastname.avail ne 'Y'} data-aom-field-not-avail{/if}>
  <td>{$lng.lbl_last_name}</td>
  <td><input type="text" name="address_book[B][lastname]" size="32" maxlength="128" value="{$cart_customer.b_lastname|escape}" {include file="main/attr_orig_data.tpl" data_orig_value=$customer.b_lastname data_orig_keep_empty='Y'} /></td>
  <td>{$customer.b_lastname}</td>
</tr>
{/if}
{if $address_fields.address.avail eq 'Y' or $customer.b_address ne ''}
<tr{cycle name=c2 values=', class="TableSubHead"'}{if $address_fields.address.avail ne 'Y'} data-aom-field-not-avail{/if}>
  <td>{$lng.lbl_address}</td>
  <td><input type="text" name="address_book[B][address]" size="32" maxlength="255" value="{$cart_customer.b_address|escape}" {include file="main/attr_orig_data.tpl" data_orig_value=$customer.b_address data_orig_keep_empty='Y'} /></td>
  <td>{$customer.b_address}</td>
</tr>
{/if}
{if $address_fields.address_2.avail eq 'Y' or $customer.b_address_2 ne ''}
<tr{cycle name=c2 values=', class="TableSubHead"'}{if $address_fields.address_2.avail ne 'Y'} data-aom-field-not-avail{/if}>
  <td>{$lng.lbl_address_2}</td>
  <td><input type="text" name="address_book[B][address_2]" size="32" maxlength="128" value="{$cart_customer.b_address_2|escape}" {include file="main/attr_orig_data.tpl" data_orig_value=$customer.b_address_2 data_orig_keep_empty='Y'} /></td>
  <td>{$customer.b_address_2}</td>
</tr>
{/if}
{if $address_fields.city.avail eq 'Y' or $customer.b_city ne ''}
<tr{cycle name=c2 values=', class="TableSubHead"'}{if $address_fields.city.avail ne 'Y'} data-aom-field-not-avail{/if}>
  <td>{$lng.lbl_city}</td>
  <td><input type="text" name="address_book[B][city]" size="32" maxlength="64" value="{$cart_customer.b_city|escape}" {include file="main/attr_orig_data.tpl" data_orig_value=$customer.b_city data_orig_keep_empty='Y'} /></td>
  <td>{$customer.b_city}</td>
</tr>
{/if}
{if $address_fields.country.avail eq 'Y' or $customer.b_country ne ''}
<tr{cycle name=c2 values=', class="TableSubHead"'}{if $address_fields.country.avail ne 'Y'} data-aom-field-not-avail{/if}>
  <td>{$lng.lbl_country}</td>
  <td>
  <select name="address_book[B][country]" id="customer_info_b_country" onchange="javascript: check_zip_code_field(this.form['address_book[B][country]'], this.form['address_book[B][zipcode]']);"{include file="main/attr_orig_data.tpl" data_orig_value=$customer.b_country data_orig_keep_empty='Y'}>
  {section name=country_idx loop=$countries}
    <option value="{$countries[country_idx].country_code|escape}"{if $cart_customer.b_country eq $countries[country_idx].country_code or ($countries[country_idx].country_code eq $config.General.default_country and $cart_customer.b_country eq "")} selected="selected"{/if}>{$countries[country_idx].country}</option>
  {/section}
  </select>
  </td>
  <td>{$customer.b_countryname}</td>
</tr>
{/if}
{if $address_fields.state.avail eq 'Y' or $customer.b_state ne ''}
<tr{cycle name=c2 values=', class="TableSubHead"'}{if $address_fields.state.avail ne 'Y'} data-aom-field-not-avail{/if}>
  <td>{$lng.lbl_state}</td>
  <td>{include file="main/states.tpl" states=$states name="address_book[B][state]" default=$cart_customer.b_state default_country=$cart_customer.b_country data_orig_value=$customer.b_state data_orig_keep_empty='Y'}</td>
  <td>{$customer.b_statename}</td>
</tr>
{/if}
{if $address_fields.county.avail eq 'Y' or $customer.b_county ne ''}
<tr{cycle name=c2 values=', class="TableSubHead"'}{if $address_fields.county.avail ne 'Y'} data-aom-field-not-avail{/if}>
  <td>{$lng.lbl_county}</td>
  <td>{include file="main/counties.tpl" counties=$counties name="address_book[B][county]" default=$cart_customer.b_county data_orig_value=$customer.b_county data_orig_keep_empty='Y'}</td>
  <td>{$customer.b_countyname}</td>
</tr>
{/if}
{if $address_fields.zipcode.avail eq 'Y' or $customer.b_zipcode ne ''}
<tr{cycle name=c2 values=', class="TableSubHead"'}{if $address_fields.zipcode.avail ne 'Y'} data-aom-field-not-avail{/if}>
    <td>{$lng.lbl_zip_code}</td>
    <td>{include file="main/zipcode.tpl" val=$cart_customer.b_zipcode zip4=$cart_customer.b_zip4 name="address_book[B][zipcode]" id="customer_info_b_zipcode" data_orig_zipcode=$customer.b_zipcode data_orig_zip4=$customer.b_zip4 data_orig_keep_empty='Y'}</td>
    <td>{include file="main/zipcode.tpl" val=$customer.b_zipcode zip4=$customer.b_zip4 static=true}</td>
</tr>
{/if}
{if $address_fields.phone.avail eq 'Y' or $customer.b_phone ne ''}
<tr{cycle name=c2 values=', class="TableSubHead"'}{if $address_fields.phone.avail ne 'Y'} data-aom-field-not-avail{/if}>
  <td>{$lng.lbl_phone}</td>
  <td><input type="text" name="address_book[B][phone]" size="32" maxlength="32" value="{$cart_customer.b_phone|escape}" {include file="main/attr_orig_data.tpl" data_orig_value=$customer.b_phone data_orig_keep_empty='Y'} /></td>
  <td>{$customer.b_phone}</td>
</tr>
{/if}
{if $address_fields.fax.avail eq 'Y' or $customer.b_fax ne ''}
<tr{cycle name=c2 values=', class="TableSubHead"'}{if $address_fields.fax.avail ne 'Y'} data-aom-field-not-avail{/if}>
  <td>{$lng.lbl_fax}</td>
  <td><input type="text" name="address_book[B][fax]" size="32" maxlength="32" value="{$cart_customer.b_fax|escape}" {include file="main/attr_orig_data.tpl" data_orig_value=$customer.b_fax data_orig_keep_empty='Y'} /></td>
  <td>{$customer.b_fax}</td>
</tr>
{/if}

{include file="modules/Advanced_Order_Management/edit_additional_fields.tpl" additional_fields=$cart_customer.additional_fields cycle_name='c2' fields_section='B' fields_filter='B' data_orig_keep_empty='Y'}

<tr>
  <td colspan="3">&nbsp;</td>
</tr>

<tr valign="top">
  <td colspan="3"><i>{$lng.lbl_shipping_address}:</i></td>
</tr>

<tr class="TableHead">
  <td height="16">&nbsp;</td>
  <th height="16" align="left">{$lng.lbl_aom_current_value}</th>
  <th height="16" align="left">{$lng.lbl_aom_original_value}</th>
</tr>
{if $address_fields.title.avail eq 'Y' or $customer.s_title ne ''}
<tr{cycle name=c3 values=', class="TableSubHead"'}{if $address_fields.title.avail ne 'Y'} data-aom-field-not-avail{/if}>
  <td>{$lng.lbl_title}</td>
  <td>
    {include file="main/title_selector.tpl" name="address_book[S][title]" id="s_title" val=$cart_customer.s_title data_orig_value=$customer.s_title data_orig_keep_empty='Y'}
  </td>
  <td>{$customer.s_title}</td>
</tr>
{/if}
{if $address_fields.firstname.avail eq 'Y' or $customer.s_firstname ne ''}
<tr{cycle name=c3 values=', class="TableSubHead"'}{if $address_fields.firstname.avail ne 'Y'} data-aom-field-not-avail{/if}>
  <td>{$lng.lbl_first_name}</td>
  <td><input type="text" name="address_book[S][firstname]" size="32" maxlength="128" value="{$cart_customer.s_firstname|escape}" {include file="main/attr_orig_data.tpl" data_orig_value=$customer.s_firstname data_orig_keep_empty='Y'} /></td>
  <td>{$customer.s_firstname}</td>
</tr>
{/if}
{if $address_fields.lastname.avail eq 'Y' or $customer.s_lastname ne ''}
<tr{cycle name=c3 values=', class="TableSubHead"'}{if $address_fields.lastname.avail ne 'Y'} data-aom-field-not-avail{/if}>
  <td>{$lng.lbl_last_name}</td>
  <td><input type="text" name="address_book[S][lastname]" size="32" maxlength="128" value="{$cart_customer.s_lastname|escape}" {include file="main/attr_orig_data.tpl" data_orig_value=$customer.s_lastname data_orig_keep_empty='Y'} /></td>
  <td>{$customer.s_lastname}</td>
</tr>
{/if}
{if $address_fields.address.avail eq 'Y' or $customer.s_address ne ''}
<tr{cycle name=c3 values=', class="TableSubHead"'}{if $address_fields.address.avail ne 'Y'} data-aom-field-not-avail{/if}>
  <td>{$lng.lbl_address}</td>
  <td><input type="text" name="address_book[S][address]" size="32" maxlength="255" value="{$cart_customer.s_address|escape}" {include file="main/attr_orig_data.tpl" data_orig_value=$customer.s_address data_orig_keep_empty='Y'} /></td>
  <td>{$customer.s_address}</td>
</tr>
{/if}
{if $address_fields.address_2.avail eq 'Y' or $customer.s_address_2 ne ''}
<tr{cycle name=c3 values=', class="TableSubHead"'}{if $address_fields.address_2.avail ne 'Y'} data-aom-field-not-avail{/if}>
  <td>{$lng.lbl_address_2}</td>
  <td><input type="text" name="address_book[S][address_2]" size="32" maxlength="128" value="{$cart_customer.s_address_2|escape}" {include file="main/attr_orig_data.tpl" data_orig_value=$customer.s_address_2 data_orig_keep_empty='Y'} /></td>
  <td>{$customer.s_address_2}</td>
</tr>
{/if}
{if $address_fields.city.avail eq 'Y' or $customer.s_city ne ''}
<tr{cycle name=c3 values=', class="TableSubHead"'}{if $address_fields.city.avail ne 'Y'} data-aom-field-not-avail{/if}>
  <td>{$lng.lbl_city}</td>
  <td><input type="text" name="address_book[S][city]" size="32" maxlength="64" value="{$cart_customer.s_city|escape}" {include file="main/attr_orig_data.tpl" data_orig_value=$customer.s_city data_orig_keep_empty='Y'} /></td>
  <td>{$customer.s_city}</td>
</tr>
{/if}
{if $address_fields.country.avail eq 'Y' or $customer.s_country ne ''}
<tr{cycle name=c3 values=', class="TableSubHead"'}{if $address_fields.country.avail ne 'Y'} data-aom-field-not-avail{/if}>
  <td>{$lng.lbl_country}</td>
  <td>
  <select name="address_book[S][country]" id="customer_info_s_country" onchange="javascript: check_zip_code_field(this.form['address_book[S][country]'], this.form['address_book[S][zipcode]']);" {include file="main/attr_orig_data.tpl" data_orig_value=$customer.s_country data_orig_keep_empty='Y'}>
  {section name=country_idx loop=$countries}
    <option value="{$countries[country_idx].country_code|escape}"{if $cart_customer.s_country eq $countries[country_idx].country_code or ($countries[country_idx].country_code eq $config.General.default_country and $cart_customer.s_country eq "")} selected="selected"{/if}>{$countries[country_idx].country}</option>
  {/section}
  </select>
  </td>
  <td>{$customer.s_countryname}</td>
</tr>
{/if}
{if $address_fields.state.avail eq 'Y' or $customer.s_statename ne ''}
<tr{cycle name=c3 values=', class="TableSubHead"'}{if $address_fields.state.avail ne 'Y'} data-aom-field-not-avail{/if}>
  <td>{$lng.lbl_state}</td>
  <td>{include file="main/states.tpl" states=$states name="address_book[S][state]" default=$cart_customer.s_state default_country=$cart_customer.s_country data_orig_value=$customer.s_state data_orig_keep_empty='Y'}</td>
  <td>{$customer.s_statename}</td>
</tr>
{/if}
{if $address_fields.county.avail eq 'Y' or $customer.s_county ne ''}
<tr{cycle name=c3 values=', class="TableSubHead"'}{if $address_fields.county.avail ne 'Y'} data-aom-field-not-avail{/if}>
  <td>{$lng.lbl_county}</td>
  <td>{include file="main/counties.tpl" counties=$counties name="address_book[S][county]" default=$cart_customer.s_county data_orig_value=$customer.s_county data_orig_keep_empty='Y'}</td>
  <td>{$customer.s_countyname}</td>
</tr>
{/if}
{if $address_fields.zipcode.avail eq 'Y' or $customer.s_zipcode ne ''}
<tr{cycle name=c3 values=', class="TableSubHead"'}{if $address_fields.zipcode.avail ne 'Y'} data-aom-field-not-avail{/if}>
    <td>{$lng.lbl_zip_code}</td>
    <td>{include file="main/zipcode.tpl" val=$cart_customer.s_zipcode zip4=$cart_customer.s_zip4 name="address_book[S][zipcode]" id="customer_info_s_zipcode" data_orig_zipcode=$customer.s_zipcode data_orig_zip4=$customer.s_zip4 data_orig_keep_empty='Y'}</td>
    <td>{include file="main/zipcode.tpl" val=$customer.s_zipcode zip4=$customer.s_zip4 static=true}</td>
</tr>
{/if}
{if $address_fields.phone.avail eq 'Y' or $customer.s_phone ne ''}
<tr{cycle name=c3 values=', class="TableSubHead"'}{if $address_fields.phone.avail ne 'Y'} data-aom-field-not-avail{/if}>
  <td>{$lng.lbl_phone}</td>
  <td><input type="text" name="address_book[S][phone]" size="32" maxlength="32" value="{$cart_customer.s_phone|escape}" {include file="main/attr_orig_data.tpl" data_orig_value=$customer.s_phone data_orig_keep_empty='Y'} /></td>
  <td>{$customer.s_phone}</td>
</tr>
{/if}
{if $address_fields.fax.avail eq 'Y' or $customer.s_fax ne ''}
<tr{cycle name=c3 values=', class="TableSubHead"'}{if $address_fields.fax.avail ne 'Y'} data-aom-field-not-avail{/if}>
  <td>{$lng.lbl_fax}</td>
  <td><input type="text" name="address_book[S][fax]" size="32" maxlength="32" value="{$cart_customer.s_fax|escape}" {include file="main/attr_orig_data.tpl" data_orig_value=$customer.s_fax data_orig_keep_empty='Y'} /></td>
  <td>{$customer.s_fax}</td>
</tr>
{/if}

{include file="modules/Advanced_Order_Management/edit_additional_fields.tpl" additional_fields=$cart_customer.additional_fields cycle_name='c3' fields_section='B' fields_filter='S' data_orig_keep_empty='Y'}

<tr>
  <td colspan="3">&nbsp;</td>
</tr>

<tr valign="top">
  <td colspan="3"><i>{$lng.lbl_additional_information}:</i></td>
</tr>
<tr class="TableHead">
  <td height="16">&nbsp;</td>
  <th height="16" align="left">{$lng.lbl_aom_current_value}</th>
  <th height="16" align="left">{$lng.lbl_aom_original_value}</th>
</tr>

{include file="modules/Advanced_Order_Management/edit_additional_fields.tpl" additional_fields=$cart_customer.additional_fields cycle_name='c4' fields_section='A' data_orig_keep_empty='Y'}

<tr>
<td colspan="3"><br />
<input type="submit" value="{$lng.lbl_update}" />
<br /><br />
</td>
</tr>

</table>
</form>
{/capture}
{include file="dialog.tpl" title=$lng.lbl_aom_edit_customer_title|substitute:"orderid":$order.orderid content=$smarty.capture.dialog extra='width="100%"'}

{include file="main/register_states.tpl" state_name="address_book[B][state]" country_name="customer_info_b_country" county_name="address_book[B][county]" state_value=$cart_customer.b_state county_value=$cart_customer.b_county}
{include file="main/register_states.tpl" state_name="address_book[S][state]" country_name="customer_info_s_country" county_name="address_book[S][county]" state_value=$cart_customer.s_state county_value=$cart_customer.s_county}

<script type="text/javascript">
//<![CDATA[
    var aom_field_state_not_avail_link = "<a class=\"aom-na\" href=\"configuration.php?option=User_Profiles\" title=\"{$lng.lbl_aom_field_not_avail}\">&nbsp;*</a>";
    var aom_field_state_not_found_link = "<a class=\"aom-nf\" href=\"configuration.php?option=User_Profiles\" title=\"{$lng.lbl_aom_field_not_found}\">&nbsp;*</a>";

    $(document).ready(function() {
       $('tr[data-aom-field-not-avail] td:first-child').append(aom_field_state_not_avail_link);
       $('tr[data-aom-field-not-found] td:first-child').append(aom_field_state_not_found_link);
    });
//]]>
</script>
