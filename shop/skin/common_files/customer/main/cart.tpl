{*
686a4c944b1bab093a0d60e12aff76ce8db05664, v24 (xcart_4_7_0), 2015-02-18 13:16:20, cart.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
<h1>{$lng.lbl_your_shopping_cart}</h1>

{if $cart ne "" and $active_modules.Gift_Certificates}
  <p class="text-block">{$lng.txt_cart_note}</p>
{/if}

{if $active_modules.POS_System ne ""}
  {include file="modules/POS_System/scan_barcode_field.tpl"}
{/if}

{capture name=dialog}

  {if $products ne ""}

    <br />

    <script type="text/javascript" src="{$SkinDir}/js/cart.js"></script>

    <div class="products cart">

      <form action="cart.php" method="post" name="cartform">

        <input type="hidden" name="action" value="update" />

        {foreach from=$products item=product}
          {if $product.hidden eq ""}

            <table cellspacing="0" class="width-100 item" summary="{$product.product|escape}">

              <tr>
                <td class="image">
                    {if $active_modules.On_Sale}
                      {include file="modules/On_Sale/on_sale_icon.tpl" product=$product module="cart"}
                    {else}
                    <a href="product.php?productid={$product.productid}">{include file="product_thumbnail.tpl" productid=$product.display_imageid product=$product.product tmbn_url=$product.pimage_url type=$product.is_pimage image_x=$product.tmbn_x}</a>
                    {/if}
                  {if $active_modules.Special_Offers ne "" and $product.have_offers}
                    {include file="modules/Special_Offers/customer/product_offer_thumb.tpl"}
                  {/if}

                </td>
                <td class="details">
                  <a href="product.php?productid={$product.productid}" class="product-title">{$product.product|amp}</a>
                  <div class="descr">{$product.descr}</div>

                  {if $product.product_options ne ""}
                    <p class="poptions-title">{$lng.lbl_selected_options}:</p>
                    <div class="poptions-list">
                      {include file="modules/Product_Options/display_options.tpl" options=$product.product_options}
                      {include file="customer/buttons/edit_product_options.tpl" id=$product.cartid}
                    </div>
                  {/if}

                  {assign var="price" value=$product.display_price}
                  {if $active_modules.Product_Configurator and $product.product_type eq "C"}
                    {include file="modules/Product_Configurator/pconf_customer_cart.tpl" main_product=$product}
                    {assign var="price" value=$product.pconf_display_price}
                  {/if}

                  {if $active_modules.Special_Offers}
                    {include file="modules/Special_Offers/customer/cart_price_special.tpl"}
                  {/if}

                  {if $active_modules.XPayments_Subscriptions and $product.subscription}
                    {include file="modules/XPayments_Subscriptions/customer/cart_product.tpl" next_date=$product.subscription.next_date}
                  {/if}

                  <span class="product-price-text">
                    {currency value=$price} x {if $active_modules.Egoods and $product.distribution}1<input type="hidden"{else}<input type="text" size="3"{/if} name="productindexes[{$product.cartid}]" value="{$product.amount}" /> = </span>
                  <span class="price">
                    {multi x=$price y=$product.amount assign=unformatted}{currency value=$unformatted}
                  </span>
                  <span class="market-price">
                    {alter_currency value=$unformatted}
                  </span>

                  {if $product.taxes}
                    {if $config.Taxes.display_taxed_order_totals eq "Y" and $config.Taxes.display_cart_products_tax_rates eq "Y"}
                      <div class="taxes">
                        {include file="customer/main/taxed_price.tpl" taxes=$product.taxes is_subtax=true}
                      </div>
                    {elseif $config.Taxes.display_cart_products_tax_rates eq "Y"}
                      <div class="taxes">
                        {include file="customer/main/tax_rates.tpl" taxes=$product.taxes}
                      </div>
                    {/if}
                  {/if}


                  {if $active_modules.Gift_Registry}
                    {include file="modules/Gift_Registry/product_event_cart.tpl"}
                  {/if}

                  {if $active_modules.Special_Offers}
                    {include file="modules/Special_Offers/customer/cart_free.tpl"}
                  {/if}

                </td>
              </tr>

              <tr>
                <td class="buttons-row">
                  {include file="customer/buttons/delete_item.tpl" href="cart.php?mode=delete&amp;productindex=`$product.cartid`" style="link" additional_button_class="simple-delete-button"}
                </td>
                <td class="buttons-row">
                  {include file="customer/buttons/button.tpl" button_title=$lng.lbl_update_item href="javascript: return updateCartItem(`$product.cartid`);" additional_button_class="light-button"}
                {if $active_modules.XAuth}
                  {include file="modules/XAuth/rpx_ss_cart_item.tpl"}
                {/if}
                </td>
              </tr>
            </table>

            <hr />

          {/if}
        {/foreach}
        
        {if $active_modules.Special_Offers}
          {include file="modules/Special_Offers/customer/free_offers.tpl"}
        {/if}

        {if $active_modules.Gift_Certificates}
          {include file="modules/Gift_Certificates/gc_cart.tpl" giftcerts_data=$cart.giftcerts}
        {/if}
        
        {include file="customer/main/cart_subtotal.tpl"}
        
        {include file="modules/Gift_Registry/gift_wrapping_cart.tpl"}

        {include file="customer/main/shipping_estimator.tpl"}

        <div class="buttons">

          <div class="left-buttons-row buttons-row">
            {include file="customer/buttons/button.tpl" type="input" button_title=$lng.lbl_update_cart}
            <div class="button-separator"></div>
            {include file="customer/buttons/button.tpl" style="link" additional_button_class="simple-delete-button" button_title=$lng.lbl_clear_cart href="javascript: if (confirm(txt_are_you_sure)) self.location='cart.php?mode=clear_cart'; return false;"}
          </div>

          <div class="right-buttons-row buttons-row">

            {if not $std_checkout_disabled}
            <div class="checkout-button">
              {if $active_modules.POS_System ne "" && $user_is_pos_operator eq "Y"}
                {include file="modules/POS_System/process_order_button.tpl" position="bottom"}
              {else}
              {include file="customer/buttons/button.tpl" button_title=$lng.lbl_checkout  href="cart.php?mode=checkout" additional_button_class="main-button"}
              {/if}
            </div>
            {/if}

            {if $active_modules.Special_Offers}
            <div class="button-separator"></div>
            {include file="modules/Special_Offers/customer/cart_checkout_buttons.tpl"}
          {/if}

          </div>

          <div class="clearing"></div>
        </div>

      </form>

      {if $paypal_express_active}
        {include file="payments/ps_paypal_pro_express_checkout.tpl" paypal_express_link="button"}

        {if $active_modules.Bill_Me_Later}
          {if $config.Bill_Me_Later.bml_enable_banners eq 'Y' and $config.Bill_Me_Later.bml_banner_on_cart eq 'inline'}
            {include file="modules/Bill_Me_Later/banner.tpl" bml_page="cart"}
          {/if}
          {include file="payments/ps_paypal_bml_button.tpl" paypal_link="button"}
        {/if}

      {/if}

      {if $amazon_enabled}
        <div class="right-box">
          {include file="modules/Amazon_Checkout/checkout_btn.tpl"}
        </div>
      {/if}

      {if $amazon_pa_enabled}
        <div class="right-box">
          {include file="modules/Amazon_Payments_Advanced/checkout_btn.tpl" btn_place="cart"}
        </div>
      {/if}

    </div>

  {else}

    {$lng.txt_your_shopping_cart_is_empty}

  {/if}

{/capture}
{include file="customer/dialog.tpl" title=$lng.lbl_items_in_cart content=$smarty.capture.dialog noborder=true}

{if $active_modules.Special_Offers and $cart ne ""}
  {include file="modules/Special_Offers/customer/cart_offers.tpl"}
  {include file="modules/Special_Offers/customer/promo_offers.tpl"}
{/if}

{if $cart.coupon_discount eq 0 and $products and $active_modules.Discount_Coupons}
  {include file="modules/Discount_Coupons/add_coupon.tpl"}
{/if}
