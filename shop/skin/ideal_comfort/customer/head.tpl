{*
2a30536f400a253c86be1a18eb00603b4ad45f18, v1 (xcart_4_5_1), 2012-06-22 11:52:57, head.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
<div class="line1">
  <div class="logo">
    <a href="{$catalogs.customer}/home.php"><img src="{$AltImagesDir}/custom/logo.png" alt="" /></a>
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
