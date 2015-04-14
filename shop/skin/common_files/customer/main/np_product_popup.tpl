{*
a8caf1146a82c7e25bc23adbf5c4c2eea754bed1, v1 (xcart_4_6_0), 2013-05-24 14:56:30, np_product_popup.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{assign var="url" value="product.php?productid=`$product.productid`"}
<div class="product-name"><a href="{$url}" class="product-title">{$product.product|amp}</a></div>
<a href="{$url}">{include file="product_thumbnail.tpl" productid=$product.productid image_x=$product.tmbn_x image_y=$product.tmbn_y product=$product.product tmbn_url=$product.tmbn_url}</a>
{if $product.appearance.has_price}
  <div class="price-row">
    <span class="price-value">{currency value=$product.taxed_price}</span>
    <span class="market-price">{alter_currency value=$product.taxed_price}</span>
  </div>
{/if}
