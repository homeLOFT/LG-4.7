{*
75e81aed7739b4fe5afcaf69020adcee08e39137, v2 (xcart_4_4_2), 2010-12-15 09:44:37, cart_price_special.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{if $product.saved_original_price and $product.special_price_used and $product.saved_original_price ne $price}
  {$lng.lbl_sp_common_price}: <span class="offers-common-price">{currency value=$product.saved_original_price}</span><br />
  {$lng.lbl_sp_special_price}:
{/if}
