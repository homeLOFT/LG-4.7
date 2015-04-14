{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, zone_element.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
<textarea cols="40" rows="{$box_size|default:3}" style="width: 100%;" name="{$name}">
{section name=id loop=$zone_elements}
{if $zone_elements[id].field_type eq $field_type}
{$zone_elements[id].field|escape}
{/if}
{/section}
</textarea>

