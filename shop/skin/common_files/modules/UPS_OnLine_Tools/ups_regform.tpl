{*
c3f2c63d6ec12340c11b3f3d5d6a44e80a517ccb, v2 (xcart_4_4_5), 2011-12-20 09:58:43, ups_regform.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{include file="check_email_script.tpl"}

<table width="100%" cellspacing="3" cellpadding="2">

<tr valign="middle">
  <td>{$lng.lbl_contact_name}:</td>
  <td class="Star" width="5">*</td>    
  <td nowrap="nowrap">
<input type="text" name="posted_data[contact_name]" size="32" maxlength="64" value="{$userinfo.contact_name|escape}" style="width: 250px;" />
{if $reg_error ne "" and $userinfo.contact_name eq ""}<font class="Star">&lt;&lt;</font>{/if}
  </td>
</tr>

<tr valign="middle">
  <td>{$lng.lbl_title}:</td>
  <td class="Star">*</td>
  <td nowrap="nowrap">
<input type="text" name="posted_data[title_name]" size="32" maxlength="64" value="{$userinfo.title_name|escape}" style="width: 250px;" />
{if $reg_error ne "" and $userinfo.title_name eq ""}<font class="Star">&lt;&lt;</font>{/if}
  </td>
</tr>

<tr valign="middle">
  <td>{$lng.lbl_company_name}:</td>
  <td class="Star">*</td>
  <td nowrap="nowrap">
<input type="text" name="posted_data[company]" size="32" maxlength="128" value="{$userinfo.company|escape}" style="width: 250px;" />
{if $reg_error ne "" and $userinfo.company eq ""}<font class="Star">&lt;&lt;</font>{/if}
  </td>
</tr>

<tr valign="middle">
  <td>{$lng.lbl_street_address}:</td>
  <td class="Star">*</td>
  <td nowrap="nowrap">
<input type="text" name="posted_data[address]" size="32" maxlength="255" value="{$userinfo.address|escape}" style="width: 250px;" />
{if $reg_error ne "" and $userinfo.address eq ""}<font class="Star">&lt;&lt;</font>{/if}
  </td>
</tr>

<tr valign="middle">
  <td>{$lng.lbl_city}:</td>
  <td class="Star">*</td>
  <td nowrap="nowrap">
<input type="text" name="posted_data[city]" size="32" maxlength="64" value="{$userinfo.city|escape}" style="width: 250px;" />
{if $reg_error ne "" and $userinfo.city eq ""}<font class="Star">&lt;&lt;</font>{/if}
  </td>
</tr>

<tr valign="middle">
  <td>{$lng.lbl_state}:</td>
  <td class="Star">*</td>
  <td nowrap="nowrap">
<select name="posted_data[state]" style="width: 250px;">
<option value="">{$lng.lbl_non_us_canada_address}</option>
{foreach key=code item=state from=$ups_states}
<option value="{$code|escape}"{if $userinfo.state eq $code} selected="selected"{/if}>{$state}</option>
{/foreach}
</select>
  </td>
</tr>

<tr valign="middle">
  <td>{$lng.lbl_country}:</td>
  <td class="Star">*</td>
  <td nowrap="nowrap">
<select name="posted_data[country]" style="width: 250px;">
<option value="">{$lng.lbl_please_select_one}</option>
{foreach key=code item=country from=$ups_countries}
<option value="{$code|escape}"{if $userinfo.country eq $code} selected="selected"{/if}>{$country}</option>
{/foreach}
</select>
{if $reg_error ne "" and $userinfo.country eq ""}<font class="Star">&lt;&lt;</font>{/if}
  </td>
</tr>

<tr valign="middle">
  <td>{$lng.lbl_zip_code}:</td>
  <td class="Star">*</td>
  <td nowrap="nowrap">
<input type="text" name="posted_data[postal_code]" size="32" maxlength="32" value="{$userinfo.postal_code|escape}" style="width: 250px;" />
{if $reg_error ne "" and $userinfo.postal_code eq ""}<font class="Star">&lt;&lt;</font>{/if}
  </td>
</tr>

<tr valign="middle">
  <td>{$lng.lbl_phone_number}:</td>
  <td class="Star">*</td>
  <td nowrap="nowrap">
<input type="text" name="posted_data[phone]" size="32" maxlength="32" value="{$userinfo.phone|escape}" style="width: 250px;" />
{if $reg_error ne "" and $userinfo.phone eq ""}<font class="Star">&lt;&lt;</font>{/if}
  </td>
</tr>

<tr>
  <td colspan="3"><b>{$lng.txt_note}:</b> {$lng.txt_ups_phone_number}</td>
</tr>

<tr valign="middle">
  <td>{$lng.lbl_web_site_url}:</td>
  <td class="Star">*</td>
  <td nowrap="nowrap">
<input type="text" name="posted_data[url]" size="32" maxlength="255" value="{$userinfo.url|escape}" style="width: 250px;" />
{if $reg_error ne "" and $userinfo.url eq ""}<font class="Star">&lt;&lt;</font>{/if}
  </td>
</tr>

<tr valign="middle">
  <td>{$lng.lbl_email_address}:</td>
  <td class="Star">*</td>
  <td nowrap="nowrap">
<input type="text" name="email" size="32" maxlength="128" value="{$userinfo.email|escape}" style="width: 250px;" />
{if $reg_error ne "" and $userinfo.email eq ""}<font class="Star">&lt;&lt;</font>{/if}
  </td>
</tr>

<tr valign="middle">
  <td>{$lng.lbl_ups_account_number}:</td>
  <td></td>
  <td nowrap="nowrap">
<input type="text" name="posted_data[shipper_number]" size="32" maxlength="16" value="{$userinfo.shipper_number|escape}" style="width: 250px;" />
  </td>
</tr>

<tr>
  <td colspan="3">
{$lng.txt_ups_account_number_note}

<br /><br /><br />

{$lng.lbl_ups_reg_contact_me}
<table>

<tr>
  <td><input type="radio" id="software_installer_yes" name="posted_data[software_installer]" value="yes"{if $userinfo.software_installer eq "yes"} checked="checked"{/if} /></td>
  <td>{$lng.lbl_yes}</td>
  <td>&nbsp;&nbsp;</td>
  <td><input type="radio" name="posted_data[software_installer]" value="no"{if $userinfo.software_installer eq "no"} checked="checked"{/if} /></td>
  <td>{$lng.lbl_no}</td>
</tr>

</table>

  </td>
</tr>

</table>
