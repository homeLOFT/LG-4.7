{*
5a039a7eae120d0096a524b4eabac03321d3220b, v4 (xcart_4_5_4), 2012-10-24 11:02:55, address_details_html.tpl, random 
vim: set ts=2 sw=2 sts=2 et:
*}
<div class="address-line">
  {if $default_fields.title and $address.title ne ''}{$address.title|escape} {/if}
  {if $default_fields.firstname and $address.firstname ne ''}{$address.firstname|escape} {/if}
  {if $default_fields.lastname and $address.lastname ne ''}{$address.lastname|escape}{/if}
</div>

<div class="address-line">
  {if $default_fields.address and $address.address ne ''}{$address.address|escape},<br />{/if}
  {if $default_fields.address_2 and $address.address_2 ne ''}{$address.address_2|escape},<br />{/if}
  {if $default_fields.city and $address.city ne ''}{$address.city|escape}, {/if}
  {if $default_fields.state and $address.state ne ''}{$address.statename|default:$address.state|escape}, {/if}
  {if $default_fields.county and  $address.county ne ''}{$address.countyname|default:$address.county|escape}, <br />{/if}
  {if $default_fields.zipcode and $address.zipcode ne ''}{include file="main/zipcode.tpl" val=$address.zipcode zip4=$address.zip4 static=true}<br />{/if}
  {if $default_fields.country and $address.country ne ''}{$address.countryname|default:$address.country|escape}{/if}
</div>

<div class="address-line">
  {if $default_fields.phone and $address.phone ne ''}{$lng.lbl_phone}: {$address.phone|escape}{/if}<br />
  {if $default_fields.fax and $address.fax ne ''}{$lng.lbl_fax}: {$address.fax|escape}{/if}
</div>

{if $additional_fields ne ''}
<div class="address-line">
  {foreach from=$additional_fields item=field}
    {if $field.avail eq 'Y' and $field.section eq 'B'}
      {if $additional_fields_type ne ''}
        {assign var='field_value' value=$field.value.$additional_fields_type}
      {else}
        {assign var='field_value' value=$field.value}
      {/if}
      {if $field_value ne ''}
        {$field.title}: {if $field.type ne 'C'}{$field_value|escape}{else}{if $field_value eq 'Y'}{$lng.lbl_yes}{else}{$lng.lbl_no}{/if}{/if}<br />
      {/if}
    {/if}
  {/foreach}
</div>
{/if}
