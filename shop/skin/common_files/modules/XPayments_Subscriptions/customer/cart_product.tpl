{*
04fde8f686ed0be7315b88835142e1a07fc30925, v1 (xcart_4_6_2), 2014-01-04 06:59:41, cart_product.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
<div class="subscription">
  <div>
    <span class="price">{$lng.lbl_xps_setup_fee}:</span>
    <span class="price-value">{currency value=$product.taxed_price}</span>
    <span class="market-price">{alter_currency value=$product.taxed_price}</span>
  </div>
  <div>
    <span class="price">{$lng.lbl_xps_subscription_fee}:</span>
    <span class="price-value">{currency value=$product.subscription.fee}</span>
    <span class="market-price">{alter_currency value=$product.subscription.fee}</span>
    <br/><span>{$product.subscription.desc}</span>
    {if $next_date}
    <br/>{$lng.lbl_xps_date_of_next_payment}: {$next_date|date_format:$config.Appearance.date_format}
    {/if}
    {if $product.subscription.rebill_periods gt 1}
      <br/><span>{$lng.lbl_xps_total_payments}: {$product.subscription.rebill_periods}</span>
    {/if}
    <br/>
  </div>
</div>
