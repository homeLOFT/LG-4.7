{*
aa912c1999988fce95a7693876fc81d5f660a1c3, v6 (xcart_4_7_0), 2015-02-13 17:02:03, cart_contents.tpl, mixon

vim: set ts=2 sw=2 sts=2 et:
*}

<table cellspacing="1" class="cart-content width-100" summary="{$lng.lbl_products|escape}">

  <tr class="head-row">
    <th>{$lng.lbl_sku}</th>
    <th class="cart-column-product">{$lng.lbl_product}</th>
    {if $cart.display_cart_products_tax_rates eq "Y"}
      <th class="cart-column-tax">
        {if $cart.product_tax_name ne ""}
          {$cart.product_tax_name}
        {else}
          {$lng.lbl_tax}
        {/if}
      </th>
    {/if}
    <th class="cart-column-price">{$lng.lbl_price}</th>
    <th>{$lng.lbl_qty}</th>
    <th class="cart-column-total">{$lng.lbl_total}</th>
  </tr>

  {foreach from=$products item=product name=products}

    <tr{interline index=$smarty.foreach.products.index total=$list_length}>
      <td>{$product.productcode}</td>
      <td>
        {$product.product|truncate:30:"...":true}
        {if $product.product_type eq "C" and $product.display_price lt 0}
          <span class="pconf-negative-price"> {$lng.lbl_pconf_discounted}</span>
        {/if}
        {if $active_modules.Gift_Registry}
          {include file="modules/Gift_Registry/product_event_cart_contents.tpl"}
        {/if}
      </td>

      {if $cart.display_cart_products_tax_rates eq "Y"}
        <td class="cart-column-tax">

          {foreach from=$product.taxes key=tax_name item=tax}
            {if $tax.tax_value gt 0}
              <div style="white-space: nowrap;">
                {if $cart.product_tax_name eq ""}
                  <span>{$tax.tax_display_name}:</span>
                {/if}
                {if $tax.rate_type eq "%"}
                  {include file="main/display_tax_rate.tpl" value=$tax.rate_value}%{else}{currency value=$tax.rate_value}
                {/if}
              </div>
            {/if}
          {/foreach}

        </td>
      {/if}

      <td class="cart-column-price cart-content-text">{currency value=$product.display_price display_sign=$product.price_show_sign}</td>
      <td class="cart-content-text">
        {$product.amount}
      </td>
      <td class="cart-column-total cart-content-text">
        {multi x=$product.display_price y=$product.amount assign="total"}
        {currency value=$total display_sign=$product.price_show_sign}
      </td>
    </tr>

  {/foreach}

  {if $active_modules.Gift_Certificates ne ""}
    {include file="modules/Gift_Certificates/gc_checkout.tpl"}
  {/if}

</table>
