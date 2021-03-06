{*
1911464f28228845e06b400a0c1330f94918227e, v2 (xcart_4_6_2), 2014-01-17 12:44:13, product_details.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
<div class="property-value">
  <span class="xps-property-name property-name property-name2">{$lng.lbl_xps_setup_fee}:</span>
  <span class="product-price-value">{currency value=$product.taxed_price-$product.subscription.fee tag_id="product_price"}</span>
  <span class="product-market-price">{alter_currency value=$product.taxed_price-$product.subscription.fee tag_id="product_alt_price"}</span>
  {if $product.taxes}
    <br />{include file="customer/main/taxed_price.tpl" taxes=$product.taxes}
  {/if}
</div>

<div class="property-value">
  <span class="xps-property-name property-name property-name2">{$lng.lbl_xps_subscription_fee}:</span>
  <span class="product-price-value">{currency value=$product.subscription.fee tag_id="subscription_fee"}</span>
  <span class="product-market-price">{alter_currency value=$product.subscription.fee tag_id="subscription_alt_fee"}</span>
</div>

<div class="property-value">
  <span class="xps-property-name property-name property-name2">&nbsp;</span>
  {$product.subscription.desc}
</div>

{if $product.subscription.rebill_periods gt 1}
<div class="property-value">
  <span class="xps-property-name property-name property-name2">&nbsp;</span>
  {$lng.lbl_xps_total_payments}: {$product.subscription.rebill_periods}
</div>
{/if}
