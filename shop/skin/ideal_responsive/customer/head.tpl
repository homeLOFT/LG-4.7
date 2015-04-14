{*
5e8f6f027e43ad9baf5123185777a0ce3103aea3, v2 (xcart_4_6_2), 2013-10-21 10:44:47, head.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
<div class="line1">
  <div class="logo">
    <a href="{$catalogs.customer}/home.php"><img src="{$AltImagesDir}/custom/logo.png" alt="{$config.Company.company_name}" /></a>
  </div>
  <div class="header-links">
		<div class="wrapper">
			{include file="customer/header_links.tpl"}
		</div>
  </div>
  {include file="customer/tabs.tpl"}

  {include file="customer/phones.tpl"}

</div>

<div class="line2">
  {if ($main ne 'cart' or $cart_empty) and $main ne 'checkout'}

    {include file="customer/search.tpl"}

    {include file="customer/language_selector.tpl"}

  {/if}
</div>

{include file="customer/noscript.tpl"}
