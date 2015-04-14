{*
aa912c1999988fce95a7693876fc81d5f660a1c3, v19 (xcart_4_7_0), 2015-02-13 17:02:03, cart_totals.tpl, mixon

vim: set ts=2 sw=2 sts=2 et:
*}
<div class="cart-totals" id="opc_totals">

  {assign var="subtotal" value=$cart.subtotal}
  {assign var="discounted_subtotal" value=$cart.discounted_subtotal}
  {assign var="shipping_cost" value=$cart.display_shipping_cost}

  <table cellspacing="0" class="totals" summary="{$lng.lbl_total|escape}">

    <tr>
      <td class="total-name">{$lng.lbl_subtotal} ( <a href="javascript:void(0);" class="dotted toggle-link" id="cart-contents-link" title="{$lng.lbl_your_cart}">{$lng.lbl_x_items|substitute:"X":$minicart_total_items}</a> ):</td>
      <td class="total-value">{currency value=$cart.display_subtotal}</td>
    </tr>

    <tr style="display:none;" id="cart-contents-box">
      <td colspan="3">
        {include file="modules/One_Page_Checkout/summary/cart_contents.tpl"}
      </td>
    </tr>

  {if $cart.discount gt 0}
    <tr>
      <td class="total-name">{$lng.lbl_discount}:</td>
      <td class="total-value discounted">{currency value=$cart.discount}</td>
    </tr>
  {/if}

  {if $cart.coupon_discount ne 0 and $cart.coupon_type ne "free_ship"}
    <tr>
      <td class="total-name dcoupons-clear">
        {$lng.lbl_discount_coupon}
        <a href="cart.php?mode=unset_coupons" class="unset-coupon-link" title="{$lng.lbl_unset_coupon|escape}"><img src="{$ImagesDir}/spacer.gif" alt="{$lng.lbl_unset_coupon|escape}" /></a>:
        <br /><span class="small">#{$cart.coupon}</span>
      </td>
      <td class="total-value discounted">{currency value=$cart.coupon_discount}</td>
    </tr>
  {/if}

  {if $cart.display_discounted_subtotal ne $cart.display_subtotal}
    <tr>
      <td class="total-name">{$lng.lbl_discounted_subtotal}:</td>
      <td class="total-value">{currency value=$cart.display_discounted_subtotal}</td>
    </tr>
  {/if}

  {if $config.Shipping.enable_shipping eq "Y"}
    <tr>
      <td class="total-name dcoupons-clear">
        {$lng.lbl_shipping_cost}{if $cart.coupon_discount ne 0 and $cart.coupon_type eq "free_ship"} ({$lng.lbl_discounted} <a href="cart.php?mode=unset_coupons" title="{$lng.lbl_unset_coupon|escape}" class="unset-coupon-link"><img src="{$ImagesDir}/spacer.gif" alt="{$lng.lbl_unset_coupon|escape}" /></a>){/if}:
      </td>

      {if ($shipping ne '' or not $need_shipping) and $userinfo ne "" or $config.General.apply_default_country eq "Y" or $cart.shipping_cost gt 0}
        <td class="total-value">{currency value=$shipping_cost}</td>
      {else}
        <td class="total-value">{$lng.txt_not_available_value}</td>
      {/if}
    </tr>
  {/if}

  {if $config.General.enable_gift_wrapping eq "Y" and $cart.need_giftwrap eq "Y"}
    {include file="modules/Gift_Registry/gift_wrapping_cart_contents.tpl"}
  {/if}

  {if $cart.taxes and $config.Taxes.display_taxed_order_totals ne "Y"}
    {foreach key=tax_name item=tax from=$cart.taxes}

      <tr>
        <td class="total-name">{$tax.tax_display_name}{if $tax.rate_type eq "%"} {include file="main/display_tax_rate.tpl" value=$tax.rate_value}%{/if}:</td>
        {if ($userinfo ne '' and not $reg_error and not $force_change_address) or $config.General.apply_default_country eq "Y"}
          <td class="total-value">{currency value=$tax.tax_cost}</td>
        {else}
          <td class="total-value">{$lng.txt_not_available_value}</td>
          {assign var="not_logged_message" value="1"}
        {/if}
      </tr>

    {/foreach}
  {/if}

  {if $cart.payment_surcharge}
    <tr>
      <td class="total-name">
        {if $cart.payment_surcharge gt 0}
          {$lng.lbl_payment_method_surcharge}
        {else}
          {$lng.lbl_payment_method_discount}
        {/if}:
      </td>
      <td class="total-value">{currency value=$cart.payment_surcharge}</td>
    </tr>
  {/if}

  {if $cart.applied_giftcerts}
    <tr>
      <td class="total-name">
        <a href="javascript:void(0);" class="dotted toggle-link" id="applied-giftcerts-link" title="{$lng.lbl_giftcert_discount|escape}">{$lng.lbl_giftcert_discount}</a>:
        <div id="applied-giftcerts-box" style="display:none;">
          {foreach from=$cart.applied_giftcerts item=gc}
            <div class="dcoupons-clear">
              {$gc.giftcert_id} : <span class="total-name">{currency value=$gc.giftcert_cost}</span>&nbsp;
              <a class="unset-gc-link" href="cart.php?mode=unset_gc&amp;gcid={$gc.giftcert_id}"><img src="{$ImagesDir}/spacer.gif" alt="{$lng.lbl_unset_gc|escape}" /></a>
            </div>
          {/foreach}
        </div>
      </td>
      <td class="total-value discounted">{currency value=$cart.giftcert_discount}</td>
    </tr>

  {/if}

  </table>

  {if $active_modules.Special_Offers ne ""}
    {include file="modules/Special_Offers/customer/cart_bonuses.tpl"}
  {/if}

  <table cellspacing="0" class="totals" summary="{$lng.lbl_total|escape}">
  <tr class="total">
    <td class="total-name">{$lng.lbl_cart_total}:</td>
    <td class="total-value nowrap">
      {currency value=$cart.total_cost}
    </td>
    {if $config.General.alter_currency_symbol ne ''}
      <td class="total-value-alt nowrap">
        {alter_currency value=$cart.total_cost}
      </td>
    {/if}
  </tr>
  </table>

  {if $active_modules.Klarna_Payments}
    {include file="modules/Klarna_Payments/monthly_cost.tpl" elementid="pp_conditions" monthly_cost=$cart.monthly_cost tag="table"}
  {/if}

  {if $paid_amount}
  <table cellspacing="0" class="totals" summary="{$lng.lbl_total|escape}">
    <tr>
      <td class="total">{$lng.lbl_paid_amount}:</td>
      <td class="total-value">
        {currency value=$paid_amount|default:$zero}
      </td>
      {if $config.General.alter_currency_symbol ne ''}
        <td class="total-value-alt">
          {alter_currency value=$paid_amount}
        </td>
      {/if}
    </tr>

    <tr>
      <td colspan="3">
        {include file="customer/main/cart_transactions.tpl" transactions=$transaction_query}
      </td>
    </tr>
  </table>
  {/if}

  {if $cart.taxes and $config.Taxes.display_taxed_order_totals eq "Y"}
  <table cellspacing="0" class="totals" summary="{$lng.lbl_taxes|escape}">
    <tr>
      <td class="total-name">
        <a href="javascript:void(0);" class="dotted toggle-link" id="order-taxes-link">{$lng.lbl_including_taxes}</a>:
        <div id="order-taxes-box" style="display:none;">
          {foreach key=tax_name item=tax from=$cart.taxes}
            {$tax.tax_display_name} ({currency value=$tax.tax_cost})<br />
          {/foreach}
        </div>
      </td>
      <td class="total-value">{currency value=$cart.tax_cost}</td>
    </tr>
  </table>
  {/if}

  <hr />

{if $cart_totals_standalone}
{load_defer_code type="css"}
{load_defer_code type="js"}
{/if}
</div>
