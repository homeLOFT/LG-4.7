{*
04fde8f686ed0be7315b88835142e1a07fc30925, v1 (xcart_4_6_2), 2014-01-04 06:59:41, setup_fee.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
<span class="price">{$lng.lbl_xps_setup_fee}:</span> <span class="price-value">{currency value=$product.taxed_price-$product.subscription.fee}</span>
<span class="market-price">{alter_currency value=$product.taxed_price-$product.subscription.fee}</span>
