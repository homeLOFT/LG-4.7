{*
1911464f28228845e06b400a0c1330f94918227e, v2 (xcart_4_6_2), 2014-01-17 12:44:13, product_details.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
<tr>
  <td class="property-name product-price" valign="top">{$lng.lbl_xps_setup_fee}:</td>
  <td class="property-value" valign="top" colspan="2">
    <span class="product-price-value">{currency value=$product.taxed_price-$product.subscription.fee tag_id="product_price"}</span>
    <span class="product-market-price">{alter_currency value=$product.taxed_price-$product.subscription.fee tag_id="product_alt_price"}</span>
    {if $product.taxes}
      <br />{include file="customer/main/taxed_price.tpl" taxes=$product.taxes}
    {/if}
  </td>
</tr>

<tr>
  <td class="property-name product-price" valign="top">{$lng.lbl_xps_subscription_fee}:</td>
  <td class="property-value" valign="top" colspan="2">
    <span class="product-price-value">{currency value=$product.subscription.fee tag_id="subscription_fee"}</span>
    <span class="product-market-price">{alter_currency value=$product.subscription.fee tag_id="subscription_alt_fee"}</span>
  </td>
</tr>

<tr>
  <td class="property-name product-price" valign="top">&nbsp;</td>
  <td class="property-value" valign="top" colspan="2">
    {$product.subscription.desc}
    {if $product.subscription.rebill_periods gt 1}
      <br/>{$lng.lbl_xps_total_payments}: {$product.subscription.rebill_periods}
    {/if}
  </td>
</tr>
