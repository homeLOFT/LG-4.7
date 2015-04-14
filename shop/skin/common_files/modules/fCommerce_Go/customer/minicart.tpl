{* 0d0465915bddae9202730b9dcd479c6724c60147, v1 (xcart_4_5_5), 2013-01-11 13:17:36, minicart.tpl, random *}
{if $cart_products}
  <div class="minicart-wrapper">

    <table cellspacing="1" class="minicart-table" summary="{$lng.lbl_fb_your_cart}">
      <tr class="header-row">
        <th style="width: 98%; text-align: left;">{$lng.lbl_fb_product}</th>
        <th style="width: 1%; text-align: center;">{$lng.lbl_fb_price}</th>
        <th style="width: 1px; text-align: center;">{$lng.lbl_fb_quantity}</th>
        <th style="width: 1%; text-align: center;">{$lng.lbl_fb_subtotal}</th>
        <th style="width: 1px;"></th>
      </tr>
      {foreach from=$cart_products item=product}
        <tr style="background-color: {cycle values="#fff, #ddd"};">
          <td class="cart-product-title">
            {$product.product}
            {if $product.product_options}
              {strip}(
              {foreach from=$product.product_options item=v name="options"}
                {$v.class}: {$v.option_name}{if !$smarty.foreach.options.last && $smarty.foreach.options.total gt 1}, {/if}
              {/foreach}
              ){/strip}
            {/if}
          </td>
          {if $active_modules.Subscriptions ne "" and $product.sub_plan ne "" and $product.product_type ne "C"}
            <td colspan="3">
              {include file="modules/Subscriptions/subscription_priceincart.tpl" product=$product}
            </td>
          {else}
            {if ($product.free_amount gt 0 && $product.subtotal eq 0) && $active_modules.Special_Offers}
              <td colspan="3" class="sp-free-item">{$lng.lbl_sp_cart_free_item}</td>
            {else}
              <td class="nowrap-cell">{include file="`$customer_dir`/currency.tpl" value=$product.display_price}</td>
              <td class="nowrap-cell" style="text-align: center;">{$product.amount}</td>
              <td{if $config.Taxes.display_taxed_order_totals neq "Y" || empty($product.taxes)} class="nowrap-cell"{/if}>
                {include file="`$customer_dir`/currency.tpl" value=$product.display_subtotal}
                {if $config.Taxes.display_taxed_order_totals eq "Y" && !empty($product.taxes)}
                  <div class="taxes">{include file="customer/main/taxed_price.tpl" taxes=$product.taxes}</div>
                {/if}
              </td>
            {/if}
          {/if}
          {assign var="product_key" value="`$product.productid``$product.add_date`"}
          <td class="nowrap-cell" style="vertical-align: middle;"><div class="delete" onclick="delete_product({$product.cartid}, {$product.productid});"></div></td>
        </tr>
      {/foreach}
      <tr>
        <td colspan="5" class="bottom-line"><hr /></td>
      </tr>
      <tr>
        <td></td>
        <td></td>
        <td align="right">{$lng.lbl_fb_total}:</td>
        <td colspan="2" class="total" style="text-align: right; padding-right: 26px; white-space: nowrap;">{include file="`$customer_dir`/currency.tpl" value=$cart_subtotal}*</td>
      </tr>

      {if $cart.taxes and $config.Taxes.display_taxed_order_totals eq "Y"}

        <tr>
          <td></td>
          <td></td>
          <td></td>
          <td align="right"><b>{$lng.lbl_including}</b></td>
          <td></td>
        </tr>

        {foreach from=$cart.taxes item=tax key=tax_name}
        <tr>
          <td></td>
          <td></td>
          <td class="nowrap-cell" align="right">{$tax.tax_display_name}:</td>
          <td class="nowrap-cell" align="right">{include file="`$customer_dir`/currency.tpl" value=$tax.tax_cost}</td>
          <td></td>
        </tr>
        {/foreach}

    {/if}

    </table>

    <div class="buttons-row right-align">

      <button class="button main-button" type="button" title="{$lng.lbl_fb_checkout_at_online_store}" onclick="javascript: top.location.href='{$current_location}/cart.php?_c={$fb_sess}';"><span><i>{$lng.lbl_fb_checkout_at_online_store}</i></span></button>

    </div>

    <p style="font-size: 9px;"><span class="star">*</span>&nbsp;{$lng.txt_fb_minicart_note}</p>

    <img src="{$ImagesDir}/spacer.gif" alt="" />

  </div>

{else}

  {$lng.lbl_fb_cart_is_empty}

{/if}

<script>
  var minicart_total_products = '{$minicart_total_products}';
</script>
