{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, help_contactus.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{include file="mail/html/mail_header.tpl"}

{include file="mail/html/responsive_row.tpl" content=$lng.eml_customers_need_help}

{include file="mail/html/responsive_row.tpl" content="<b>`$lng.lbl_customer_info`:</b>"}

<table class="block-grid data-table">
{if $is_areas.C}

{if $default_fields.firstname.avail eq 'Y'}
<tr>
<td class="name"><b>{$lng.lbl_first_name}:</b></td>
<td class="value">{$contact.firstname}</td>
</tr>
{/if}
{if $default_fields.lastname.avail eq 'Y'}
<tr>
<td class="name"><b>{$lng.lbl_last_name}:</b></td>
<td class="value">{$contact.lastname}</td>
</tr>
{/if}
{if $default_fields.company.avail eq 'Y'}
<tr>
<td class="name"><b>{$lng.lbl_company}:</b></td>
<td class="value">{$contact.company}</td>
</tr>
{/if}
{if $default_fields.email.avail eq 'Y'}
<tr>
<td class="name"><b>{$lng.lbl_email}:</b></td>
<td class="value">{$contact.email}</td>
</tr>
{/if}
{if $default_fields.url.avail eq 'Y'}
<tr>
<td class="name"><b>{$lng.lbl_web_site}:</b></td>
<td class="value">{$contact.url}</td>
</tr>
{/if}

{/if}

{if $is_areas.A}
<tr>
<td colspan="2" class="section">
</td>
</tr>
{if $default_fields.address.avail eq 'Y' or $default_fields.address_2.avail eq 'Y'}
<tr>
<td class="name"><b>{$lng.lbl_address}:</b></td>
<td class="value">{$contact.address}<br />{$contact.address_2}</td>
</tr>
{/if}
{if $default_fields.city.avail eq 'Y'}
<tr>
<td class="name"><b>{$lng.lbl_city}:</b></td>
<td class="value">{$contact.city}</td>
</tr>
{/if}
{if $default_fields.county.avail eq 'Y' and $config.General.use_counties eq "Y"}
<tr>
<td class="name"><b>{$lng.lbl_county}:</b></td>
<td class="value">{$contact.countyname}</td>
</tr>
{/if}
{if $default_fields.state.avail eq 'Y'}
<tr>
<td class="name"><b>{$lng.lbl_state}:</b></td>
<td class="value">{$contact.statename}</td>
</tr>
{/if}
{if $default_fields.country.avail eq 'Y'}
<tr>
<td class="name"><b>{$lng.lbl_country}:</b></td>
<td class="value">{$contact.countryname}</td>
</tr>
{/if}
{if $default_fields.zipcode.avail eq 'Y'}
<tr>
<td class="name"><b>{$lng.lbl_zip_code}:</b></td>
<td class="value">{include file="main/zipcode.tpl" val=$contact.zipcode zip4=$contact.zip4 static=true}</td>
</tr>
{/if}

{if $default_fields.phone.avail eq 'Y'}
<tr>
<td class="name"><b>{$lng.lbl_phone}:</b></td>
<td class="value">{$contact.phone}</td>
</tr>
{/if}
{if $default_fields.fax.avail eq 'Y'}
<tr>
<td class="name"><b>{$lng.lbl_fax}:</b></td>
<td class="value">{$contact.fax}</td>
</tr>
{/if}
{/if}

{if $additional_fields ne ''}
{foreach from=$additional_fields item=v}
<tr>
<td class="name"><b>{$v.title}:</b></td>
<td class="value">{$v.value}</td>
</tr>
{/foreach}
{/if}

{if $default_fields.department.avail eq 'Y'}
<tr>
<td class="name"><b>{$lng.lbl_department}:</b></td>
<td class="value">{$contact.department}</td>
</tr>
{/if}


<tr>
<td class="name"><b>{$lng.lbl_subject}:</b></td>
<td class="value">{$contact.subject}</td>
</tr>
</table>

{capture name="row"}
<b>{$lng.lbl_message}:</b><br />
<hr size="1" noshade="noshade" color="#DDDDDD" align="left" />
{/capture}
{include file="mail/html/responsive_row.tpl" content=$smarty.capture.row}

{capture name="row"}
<i>{$contact.body|nl2br}</i>
{/capture}
{include file="mail/html/responsive_row.tpl" content=$smarty.capture.row}

{include file="mail/html/signature.tpl"}
