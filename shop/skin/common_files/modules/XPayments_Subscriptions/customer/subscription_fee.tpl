{*
04fde8f686ed0be7315b88835142e1a07fc30925, v1 (xcart_4_6_2), 2014-01-04 06:59:41, subscription_fee.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
<div class="price-row">
  <span class="price">{$lng.lbl_xps_subscription_fee}:</span> <span class="price-value">{currency value=$product.subscription.fee}</span>
  <span class="market-price">{alter_currency value=$product.subscription.fee}</span>
  <br/><span>{$product.subscription.desc}</span>
  {if $product.subscription.rebill_periods gt 1}
    <br/><span>{$lng.lbl_xps_total_payments}: {$product.subscription.rebill_periods}</span>
  {/if}
</div>
