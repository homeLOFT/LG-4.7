{*
16fd72c950254a8d4861815dac0d6147af93e43f, v11 (xcart_4_7_0), 2014-12-23 10:13:26, products_t.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
<div class="products products-list products-div">
  {foreach from=$products item=product name=products}<div{interline name=products foreach_iteration="`$smarty.foreach.products.iteration`" foreach_total="`$smarty.foreach.products.total`" additional_class="item"}>
    <div class="item-box">

<script type="text/javascript">
//<![CDATA[
  products_data[{$product.productid}] = {ldelim}{rdelim};
//]]>
</script>

    {if $active_modules.Product_Configurator and $is_pconf and $current_product}
      {assign var="url" value="product.php?productid=`$product.productid`&amp;pconf=`$current_product.productid`&amp;slot=`$slot`"}
    {else}
      {assign var="url" value="product.php?productid=`$product.productid`&amp;cat=`$cat`&amp;page=`$navigation_page`"}
      {if $featured eq 'Y'}
        {assign var="url" value=$url|cat:"&amp;featured=Y"}
      {/if}
    {/if}
    
      <div class="image">
        <div class="image-wrapper"{if $config.Appearance.thumbnail_height gt 0 or $product.tmbn_y gt 0 or $max_images_height gt 0} style="height: {$max_images_height|default:$config.Appearance.thumbnail_height|default:$product.tmbn_y}px;line-height: {$max_images_height|default:$config.Appearance.thumbnail_height|default:$product.tmbn_y}px;"{/if}>
          {if $active_modules.On_Sale}
            {include file="modules/On_Sale/on_sale_icon.tpl" product=$product module="products_t" href=$url}
          {else}
            <a href="{$url}"{if $config.Appearance.thumbnail_height gt 0 or $product.tmbn_y gt 0 or $max_images_height gt 0} style="height: {$max_images_height|default:$config.Appearance.thumbnail_height|default:$product.tmbn_y}px;"{/if}>{include file="product_thumbnail.tpl" productid=$product.productid image_x=$product.tmbn_x image_y=$product.tmbn_y product=$product.product tmbn_url=$product.tmbn_url}</a>
          {/if}

          {if $active_modules.Special_Offers and $product.have_offers}
            {include file="modules/Special_Offers/customer/product_offer_thumb.tpl"}
          {/if}
		    </div>

        {if $product.rating_data}
          {include file="modules/Customer_Reviews/vote_bar.tpl" rating=$product.rating_data productid=$product.productid}
        {/if}
      </div>

      <div class="details">

        <a href="{$url}" class="product-title">{$product.product|amp}</a>

        {if $active_modules.New_Arrivals}
          {include file="modules/New_Arrivals/new_arrivals_show_date.tpl" product=$product}
        {/if}

        {if $active_modules.Advanced_Customer_Reviews}
          {include file="modules/Advanced_Customer_Reviews/acr_products_list.tpl"}
        {/if}

        {if $config.Appearance.display_productcode_in_list eq "Y" and $product.productcode ne ""}
          <div class="sku">{$lng.lbl_sku}: <span class="sku-value">{$product.productcode|escape}</span></div>
        {/if}

        {if $product.product_type eq "C"}

          <div class="separator">&nbsp;</div>
          {include file="customer/buttons/details.tpl" href=$url}

        {else}

          <div class="price-cell">

            {if not $product.appearance.is_auction}

              {if $product.appearance.has_price}

                <div class="price-row{if $active_modules.Special_Offers ne "" and $product.use_special_price ne ""} special-price-row{/if}">
                  {if $active_modules.XPayments_Subscriptions and $product.subscription}
                    {include file="modules/XPayments_Subscriptions/customer/setup_fee.tpl"}
                  {else}
                  <span class="price-value">{currency value=$product.taxed_price}</span>
                  <span class="market-price">{alter_currency value=$product.taxed_price}</span>
                  {/if}
                  {if $active_modules.Klarna_Payments}
                    {include file="modules/Klarna_Payments/monthly_cost.tpl" elementid="pp_conditions`$product.productid`" monthly_cost=$product.monthly_cost products_list='Y'}
                  {/if}

                  {if $active_modules.Product_Notifications ne '' and $config.Product_Notifications.prod_notif_enabled_P eq 'Y' and $config.Product_Notifications.prod_notif_show_in_list_P eq 'Y'}
                    {include file="modules/Product_Notifications/product_notification_request_button.tpl" productid=$product.productid type="P"}
                  {/if}
                </div>
                {if $active_modules.XPayments_Subscriptions and $product.subscription}
                  {include file="modules/XPayments_Subscriptions/customer/subscription_fee.tpl"}
                {/if}

                {if $active_modules.Product_Notifications ne '' and $config.Product_Notifications.prod_notif_enabled_P eq 'Y' and $config.Product_Notifications.prod_notif_show_in_list_P eq 'Y'}
                  {include file="modules/Product_Notifications/product_notification_request_form.tpl" productid=$product.productid variantid=$product.variantid|default:0 type="P"}
                {/if}

                {if $product.appearance.has_market_price and $product.appearance.market_price_discount gt 0}
                  <div class="market-price">
                    {$lng.lbl_market_price}: <span class="market-price-value">{currency value=$product.list_price}</span>

                    {if $config.General.alter_currency_symbol ne ""}, {/if}
                    <span class="price-save">{$lng.lbl_save_price} {$product.appearance.market_price_discount}%</span>

                  </div>
                {/if}

                {if $product.taxes}
                  <div class="taxes">
                    {include file="customer/main/taxed_price.tpl" taxes=$product.taxes is_subtax=true}
                  </div>
                {/if}

              {/if}

              {if $active_modules.Special_Offers ne "" and $product.use_special_price ne ""}
                {include file="modules/Special_Offers/customer/product_special_price.tpl"}
              {/if}

            {else}

              <span class="price">{$lng.lbl_enter_your_price}</span><br />
              {$lng.lbl_enter_your_price_note}

            {/if}

          </div>

          <div class="buttons-cell">
            {if $active_modules.Product_Configurator and $is_pconf and $current_product}
              {include file="modules/Product_Configurator/pconf_add_form.tpl"}
            {elseif $product.appearance.buy_now_enabled and $product.product_type ne "C"}
              {if $login ne ""}
                {include_cache file="customer/main/buy_now.tpl" product=$product cat=$cat featured=$featured is_matrix_view=$is_matrix_view login="1" smarty_get_cat=$smarty.get.cat smarty_get_page=$smarty.get.page smarty_get_quantity=$smarty.get.quantity}
              {else}
                {include_cache file="customer/main/buy_now.tpl" product=$product cat=$cat featured=$featured is_matrix_view=$is_matrix_view login="" smarty_get_cat=$smarty.get.cat smarty_get_page=$smarty.get.page smarty_get_quantity=$smarty.get.quantity}
              {/if}
            {/if}
          </div>

          <div class="clearing"></div>
		 
        {/if}

      </div>

      {if $active_modules.Feature_Comparison}
        {include file="modules/Feature_Comparison/compare_checkbox.tpl"}
      {/if}

    </div>

    <div class="clearing"></div>

    {if not ($active_modules.Product_Configurator and $is_pconf and $current_product) and ($product.appearance.buy_now_enabled and $product.product_type ne "C")}
      {if $active_modules.Socialize}
        <div class="item-box list-soc-buttons">
          {include file="modules/Socialize/buttons_row.tpl" matrix=$is_matrix_view href=$product.productid}
        </div>
        <div class="clearing"></div>
      {/if}
    {/if}
  </div>{/foreach}
</div>
<div class="clearing"></div>
