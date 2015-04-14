{*
514f972cb484a86ed189e3f31934b9a2c1ed8b47, v4 (xcart_4_6_4), 2014-06-30 13:29:54, help_contactus.tpl, mixon
vim: set ts=2 sw=2 sts=2 et:
*}
{include file="mail/mail_header.tpl"}


{$lng.eml_customers_need_help}

{if $is_areas.C}
{$lng.lbl_customer_info}:
---------------------
{if $default_fields.title.avail eq 'Y'}{$lng.lbl_title|mail_truncate}{$contact.title}
{/if}
{if $default_fields.firstname.avail eq 'Y'}{$lng.lbl_first_name|mail_truncate}{$contact.firstname}
{/if}
{if $default_fields.lastname.avail eq 'Y'}{$lng.lbl_last_name|mail_truncate}{$contact.lastname}
{/if}
{if $default_fields.company.avail eq 'Y'}{$lng.lbl_company|mail_truncate}{$contact.company}
{/if}
{if $default_fields.email.avail eq 'Y'}{$lng.lbl_email|mail_truncate}{$contact.email}
{/if}
{if $default_fields.url.avail eq 'Y'}{$lng.lbl_web_site|mail_truncate}{$contact.url}
{/if}

{/if}
{if $is_areas.A}
{$lng.lbl_address}:
----------------
{if $default_fields.address.avail eq 'Y'}{$lng.lbl_address|mail_truncate}{$contact.address}
{/if}
{if $default_fields.address_2.avail eq 'Y'}{$lng.lbl_address_2|mail_truncate}{$contact.address_2}
{/if}
{if $default_fields.city.avail eq 'Y'}{$lng.lbl_city|mail_truncate}{$contact.city}
{/if}
{if $default_fields.county.avail eq 'Y' and $config.General.use_counties eq "Y"}
{$lng.lbl_county|mail_truncate}{$contact.countyname}
{/if}
{if $default_fields.state.avail eq 'Y'}{$lng.lbl_state|mail_truncate}{$contact.statename}
{/if}
{if $default_fields.country.avail eq 'Y'}{$lng.lbl_country|mail_truncate}{$contact.countryname}
{/if}
{if $default_fields.zipcode.avail eq 'Y'}{$lng.lbl_zip_code|mail_truncate}{include file="main/zipcode.tpl" val=$contact.zipcode zip4=$contact.zip4 static=true}
{/if}

{if $default_fields.phone.avail eq 'Y'}{$lng.lbl_phone|mail_truncate}{$contact.phone}
{/if}
{if $default_fields.fax.avail eq 'Y'}{$lng.lbl_fax|mail_truncate}{$contact.fax}
{/if}
{/if}
{if $additional_fields ne ''}

{foreach from=$additional_fields item=v}
{$v.title|mail_truncate}{$v.value}
{/foreach}
{/if}

{if $default_fields.department.avail eq 'Y'}{$lng.lbl_department|mail_truncate}{$contact.department}
{/if}
{$lng.lbl_subject|mail_truncate}{$contact.subject}
{$lng.lbl_message}:
{$contact.body}

{include file="mail/signature.tpl"}
