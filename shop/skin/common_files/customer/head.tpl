{*
b5c36b3069740cf8dd17b81c4136b8aaa2d5fb92, v3 (xcart_4_7_2), 2015-04-24 12:06:42, head.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
<div class="line1">
  <div class="logo">
    <a href="{$catalogs.customer}/home.php"><img src="{$ImagesDir}/xlogo.gif" alt="{$config.Company.company_name}" /></a>
  </div>

  {include file="customer/tabs.tpl"}

  {include file="customer/phones.tpl"}

</div>

<div class="line2">
  {if ($main ne 'cart' or $cart_empty) and $main ne 'checkout'}

    {include file="customer/search.tpl"}

    {include file="customer/language_selector.tpl"}

  {else}

    {include file="modules/`$checkout_module`/head.tpl"}

  {/if}
</div>

{include file="customer/noscript.tpl"}
