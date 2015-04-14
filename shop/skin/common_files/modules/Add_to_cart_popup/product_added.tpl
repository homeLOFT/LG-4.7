{*
ba13c258746d2b6726f772cad5f8c7d607e2edd5, v13 (xcart_4_7_0), 2015-02-27 15:55:06, product_added.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}

<div>

  <table cellspacing="5" cellpadding="0" id="Add_to_cart_popup_window">
    <tr>
      <td valign="middle" width="1%">

        <div class="thumbnail">
          <a href="{$product_url}">{include file="product_thumbnail.tpl" productid=$product.productid image_x=$product.image_x image_y=$product.image_y product=$product.product tmbn_url=$product.image_url type=$product.image_type}</a>
        </div>

      </td>

      <td valign="middle" width="90%">

        <div class="details">

          <div class="title">
            {$add_product.amount} x <a href="{$product_url}">{$product.product}</a>
          </div>

          {if $product_options}
            <div class="options">
              {include file="modules/Product_Options/display_options.tpl" options=$product_options}
            </div>
          {/if}

          <div class="price">
            <span class="product-price-value">{currency value=$product.taxed_price}</span>
            <span class="product-alt-price-value">{alter_currency value=$product.taxed_price}</span>
          </div>

        </div>

      </td>

      <td valign="middle">

        <div class="cart-outer">
          <div class="cart">
            <div class="header">{$lng.lbl_your_cart}</div>
            <ul>
              <li>
                <span class="label">{$lng.lbl_items}:</span> {$minicart_total_items}
              </li>
              <li>
                <span class="label">{$lng.lbl_subtotal}:</span> {currency value=$minicart_total_cost}
              </li>
            </ul>
            <a href="cart.php" class="view-cart">{$lng.lbl_view_cart}</a>
          </div>
        </div>

      </td>
    </tr>

    <tr>
      <td colspan="3" align="center">
        <hr />
        <table>
          <tr>
            <td class="buttons_line">
              <a href="#" class="continue-shopping">{$lng.lbl_continue_shopping}</a>
              <span class="button-spacer"></span>
              <a href="cart.php?mode=checkout" class="proceed-to-checkout" autofocus >{$lng.lbl_proceed_to_checkout}</a>
            </td>
          </tr>
          {if $paypal_express_active}
            <tr>
              <td id="alternative_checkouts">
               {include file="payments/ps_paypal_pro_express_checkout.tpl" paypal_express_link="button"}
              </td>
            </tr>
          {/if}
          {if $amazon_pa_enabled}
            <tr>
              <td align="right">
                {include file="modules/Amazon_Payments_Advanced/checkout_btn.tpl" btn_place="add2cart_popup"}
              </td>
            </tr>
          {/if}
        </table>
      </td>
    </tr>
    
    {if $upselling}
    <tr>
      <td colspan="3">
        <table cellspacing="0" cellpadding="0" width="100%" class="upselling details products-table">

        <tr>
          <td colspan="{$upselling|@count}">
            <h1>{$lng.lbl_you_may_also_like}</h1>
          </td>
        </tr>

        <tr>
          {foreach from=$upselling item=p}
            <td align="center" width="33%">
              {if $p}
                <a href="{$p.product_url}">{include file="product_thumbnail.tpl" productid=$p.productid image_x=$p.tmbn_x image_y=$p.tmbn_y product=$p.product tmbn_url=$p.tmbn_url}</a>
              {/if}
            </td>
          {/foreach}
        </tr>

        <tr>
          {foreach from=$upselling item=p}
            <td align="center" width="33%" valign="top">
              {if $p}
                <div class="title">
                  <a href="{$p.product_url}">{$p.product}</a>
                </div>
              {/if}
            </td>
          {/foreach}
        </tr>

        <tr>
          {foreach from=$upselling item=p}
            <td align="center" width="33%">
              {if $p}
                <div class="price">
                  <span class="product-price-value">{currency value=$p.taxed_price}</span>
                  <span class="product-alt-price-value">{alter_currency value=$p.taxed_price}</span>
                </div>
              {/if}
            </td>
          {/foreach}
        </tr>

        <tr>
          {foreach from=$upselling item=p}
            <td align="center" width="33%" class="product-cell product-cell-buynow">
              {if $p}
                <div class="buy product-input">
                  {include file="modules/Add_to_cart_popup/buy.tpl" is_matrix_view=1}
                </div>
              {/if}
            </td>
          {/foreach}
        </tr>

        </table>
      </td>
    </tr>
    {/if}

  </table>

{load_defer_code type="css"}
{load_defer_code type="js"}
</div>
