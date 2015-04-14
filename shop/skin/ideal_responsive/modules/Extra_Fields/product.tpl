{*
a9ecf7b17b8bd562cfbb157f528bbf2b762ab2a0, v2 (xcart_4_6_5), 2014-09-09 08:29:08, product.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{foreach from=$extra_fields item=v}
  {if $v.active eq 'Y' and $v.is_value eq 'Y'}
    <div class="property-name">{$v.field}</div>
    <div class="property-value" colspan="2">{$v.field_value}</div>
    <div class="separator"></div>
  {/if}
{/foreach}
