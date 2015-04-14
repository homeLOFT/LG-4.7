{*
ba13c258746d2b6726f772cad5f8c7d607e2edd5, v10 (xcart_4_7_0), 2015-02-27 15:55:06, minicart.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
<div class="minicart-box">

  <form action="{$xcart_web_dir}/cart.php" method="post" name="minicartform">
    <input type="hidden" name="action" value="update" />

    <ul class="cart-items">

      {foreach from=$products item=product name=products}

        {if $product.hidden eq ''}

        <li{interline index=$smarty.foreach.products.index total=$list_length}>

          <a href="{$xcart_web_dir}/product.php?productid={$product.productid}">{$product.product}</a>

          {if $active_modules.Product_Configurator and $product.product_type eq "C"}
            {assign var="price" value=$product.pconf_display_price}
          {else}
            {assign var="price" value=$product.display_price}
          {/if}

          <div class="price-row">

            {if $active_modules.Egoods and $product.distribution}

              <span class="quantity">
                1
                <input type="hidden" name="productindexes[{$product.cartid}]" value="1" />
              </span>

            {else}

              <input type="text" name="productindexes[{$product.cartid}]" value="{$product.amount}" class="quantity" />
            {/if}
            x
            <span class="price">{currency value=$price}</span>
            =
            <span class="total">{multi x=$price y=$product.amount assign=unformatted}{currency value=$unformatted}</span>

            <a href="{$xcart_web_dir}/cart.php?mode=delete&amp;productindex={$product.cartid}" class="delete" title="{$lng.lbl_delete_item|escape}"><img src="{$ImagesDir}/spacer.gif" alt="" /></a>
          </div>

        </li>

        {/if}

      {/foreach}

      {foreach from=$giftcerts item=gc key=gcindex name=giftcerts}

        <li{interline index=$smarty.foreach.giftcerts.index total=$list_length}>
          <a href="{$xcart_web_dir}/giftcert.php?gcindex={$gcindex}">{$lng.lbl_gift_certificate}</a>
          <a href="{$xcart_web_dir}/giftcert.php?mode=delgc&amp;gcindex={$gcindex}" class="delete" title="{$lng.lbl_delete_item|escape}"><img src="{$ImagesDir}/spacer.gif" alt="" /></a>
          <br />
          <table cellspacing="1" cellpadding="2">
            <tr>
              <td>{$lng.lbl_recipient}:</td>
              <td>{$gc.recipient}</td>
            </tr>
            <tr>
              <td>{$lng.lbl_amount}:</td>
              <td>{currency value=$gc.amount}</td>
            </tr>
          </table>
        </li>    

      {/foreach}

      {if $cart_not_full}
        <li class="dots">&hellip;</li>
        <li><a href="{$xcart_web_dir}/cart.php">{$lng.lbl_other_products_in_cart}</a></li>
      {/if}

    </ul>

    <hr />

    <div class="left-buttons-row buttons-row hidden">
      {include file="customer/buttons/update.tpl" type="input" additional_button_class="update-cart light-button"}
      <div class="button-separator"></div>
      {include file="customer/buttons/button.tpl" button_title=$lng.lbl_clear_cart href="`$xcart_web_dir`/cart.php?mode=clear_cart" additional_button_class="clear-cart light-button"}
    </div>

  </form>

   <ul class="menu">
      <li class="view-cart-link"><a href="{$xcart_web_dir}/cart.php">{$lng.lbl_view_cart}</a></li>

      {if $paypal_express_active}

        <li class="checkout-popup-link">
          <a href="{$xcart_web_dir}/cart.php?mode=checkout" class="link"><span>{$lng.lbl_checkout}</span><img src="{$ImagesDir}/spacer.gif" alt="" /></a>

          <div class="buttons-box">
            <div class="left-buttons-row buttons-row">
              {include file="customer/buttons/button.tpl" button_title=$lng.lbl_checkout href="`$xcart_web_dir`/cart.php?mode=checkout" additional_button_class="minicart-checkout-button"}
            </div>

            {if $paypal_express_active}
              {include file="payments/ps_paypal_pro_express_checkout.tpl" paypal_express_link="button"}
              {if $active_modules.Bill_Me_Later}
                {include file="payments/ps_paypal_bml_button.tpl" paypal_link="button"}
              {/if}
            {/if}

          </div>

        </li>

      {else}

        <li class="checkout-link"><a href="{$xcart_web_dir}/cart.php?mode=checkout">{$lng.lbl_checkout}</a></li>

      {/if}

      {if $amazon_pa_enabled}
        <li class="checkout-link">
          {include file="modules/Amazon_Payments_Advanced/checkout_btn.tpl" btn_place="minicart"}
        </li>
      {/if}

    </ul>

{if $minicart_standalone}
  {load_defer_code type="css"}
  {load_defer_code type="js"}
{/if}
</div>
