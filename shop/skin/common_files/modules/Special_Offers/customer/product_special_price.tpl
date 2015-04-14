{*
42dbc008ecfc3adc1dfd3b61f447ff8fef9436a7, v3 (xcart_4_6_1), 2013-09-03 16:20:11, product_special_price.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
<p class="offers-price" id="p_offers_price{$product.productid}">

  {$lng.lbl_sp_special_price}: 
  {if $product.special_price gt 0}
    {currency value=$product.special_price}
  {else}
    {$lng.lbl_sp_special_price_free} ({currency value=$product.special_price})
  {/if}

</p>
