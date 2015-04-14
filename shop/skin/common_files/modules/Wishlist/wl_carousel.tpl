{*
16fd72c950254a8d4861815dac0d6147af93e43f, v9 (xcart_4_7_0), 2014-12-23 10:13:26, wl_carousel.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{if $products || $giftcerts}

<div class="products wl-carousel">

  <div class="carousel jcarousel-skin-tango" id="wl_carousel">

    <ul>
      {if $products}
      {foreach from=$products item=product name=products}
        <li{interline name=products foreach_iteration="`$smarty.foreach.products.iteration`" foreach_total="`$smarty.foreach.products.total`"} style="width: {$max_wl_width+10}px;">
          <div>
            <form action="cart.php" method="post" name="update{$product.wishlistid}_form">

              <input type="hidden" name="mode" value="wishlist" />
              <input type="hidden" name="eventid" value="{$eventid|escape}" />
              <input type="hidden" name="wlitem" value="{$product.wishlistid}" />
              <input type="hidden" name="action" value="update_quantity" />

              <div class="image" style="width: {$max_wl_width+10}px; height: {$max_wl_height+10}px;">
                  {if $active_modules.On_Sale}
                    {include file="modules/On_Sale/on_sale_icon.tpl" product=$product module="wl_carousel"}
                  {else}
                  <a href="product.php?productid={$product.productid}">{include file="product_thumbnail.tpl" productid=$product.productid image_x=$product.tmbn_x image_y=$product.tmbn_y product=$product.product tmbn_url=$product.tmbn_url}</a>
                  {/if}
  
                {if $active_modules.Special_Offers and $product.have_offers}
                  {include file="modules/Special_Offers/customer/product_offer_thumb.tpl"}
                {/if}
              </div>

              <a href="product.php?productid={$product.productid}" class="product-title">{$product.product|amp}</a><br />

              <div class="price-row">
                {currency value=$product.taxed_price} <span class="wl-qty">x {$product.amount}</span>
                <a href="{$xcart_web_dir}/cart.php?mode=wldelete&amp;wlitem={$product.wishlistid}&amp;eventid={$eventid}&amp;pindex" class="delete" title="{$lng.lbl_delete_item|escape}"><img src="{$ImagesDir}/spacer.gif" alt="" /></a>
              </div>
            
              {if ((($wl_products and ($product.amount_purchased lt $product.amount or $eventid eq "") and $product.avail gt "0") or $config.General.unlimited_products eq "Y") or $main_mode eq "manager" or $product.product_type eq "C") and ($login or $giftregistry ne "")}

                {if $giftregistry eq ""}
                  {include file="customer/buttons/button.tpl" button_title=$lng.lbl_add_to_cart href="javascript: self.location = 'cart.php?mode=wl2cart&amp;wlitem=`$product.wishlistid`&amp;amount=`$product.amount`'"}
                {else}
                  {include file="customer/buttons/button.tpl" button_title=$lng.lbl_add_to_cart href="javascript: self.location = 'cart.php?mode=wl2cart&amp;fwlitem=`$product.wishlistid`&amp;eventid=`$eventid`&amp;amount=`$product.amount`'"}
                {/if}
              {/if}

            </form>

          </div>
            
        </li>
      {/foreach}  
      {/if}
      {if $giftcerts}
      {foreach from=$giftcerts item=gc name=giftcerts key=gcindex}

        <li{interline name=products foreach_iteration="`$smarty.foreach.products.iteration`" foreach_total="`$smarty.foreach.products.total`" additional_class=giftcert-item} style="width: {$max_wl_width+20}px;">
          <div>
            <form action="cart.php" method="post" name="update{$product.wishlistid}_form">

              <div class="item image" style="width: {$max_wl_width+10}px; height: {$max_wl_height+10}px;">
                <img src="{$ImagesDir}/spacer.gif" alt="" />
              </div>

               <span class="product-title">{$lng.lbl_gift_certificate}</span><br />


          {if $g.amount_purchased gt 1}
            <div class="product-details-title">{$lng.lbl_purchased}</div>
          {/if}

          <div class="giftcert-item-row">
            <span class="giftcert-item-subtitle">{$lng.lbl_recipient}:</span>
            {$gc.recipient}
          </div>

          {if $gc.send_via eq "E"}
            <div class="giftcert-item-row">
              <span class="giftcert-item-subtitle">{$lng.lbl_email}:</span>
              {$gc.recipient_email}
            </div>

          {elseif $gc.send_via eq "P"}

            <div class="giftcert-item-row">
              <span class="giftcert-item-subtitle">{$lng.lbl_mail_address}:</span>
              {$gc.recipient_address}, {$gc.recipient_city}, {if $config.General.use_counties eq "Y"}{$gc.recipient_countyname} {/if}{$gc.recipient_state} {$gc.recipient_country} {include file="main/zipcode.tpl" val=$giftcert.recipient_zipcode zip4=$giftcert.recipient_zip4 static=true}
            </div>

            {if $gc.recipient_phone}
              <div class="giftcert-item-row">
                <span class="giftcert-item-subtitle">{$lng.lbl_phone}:</span>
                {$gc.recipient_phone}
              </div>
            {/if}

           {/if}

              <div class="price-row">
                {currency value=$gc.amount}
                <a href="{$xcart_web_dir}/cart.php?mode=wldelete&amp;wlitem={$gc.wishlistid}&amp;eventid={$eventid}&amp;pindex" class="delete" title="{$lng.lbl_delete_item|escape}"><img src="{$ImagesDir}/spacer.gif" alt="" /></a>
              </div>

            {if $login}

              {if $giftregistry eq ""}
                {include file="customer/buttons/button.tpl" button_title=$lng.lbl_add_to_cart href="javascript: self.location = 'cart.php?mode=wl2cart&amp;wlitem=`$gc.wishlistid`'"}
              {else}
                {include file="customer/buttons/button.tpl" button_title=$lng.lbl_add_to_cart href="javascript: self.location = 'cart.php?mode=wl2cart&amp;wlitem=`$gc.wishlistid`&amp;eventid=`$eventid`'"}
              {/if}

            {/if}



            </form>

          </div>
        </li>

      {/foreach}  
      {/if}

    </ul>

  </div>

</div>  
<div class="clearing"></div>

{/if}
