{*
16fd72c950254a8d4861815dac0d6147af93e43f, v15 (xcart_4_7_0), 2014-12-23 10:13:26, products_t.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{list2matrix assign="products_matrix" assign_width="cell_width" list=$products row_length=$config.Appearance.products_per_row}
{assign var="is_matrix_view" value=true}

{if $products_matrix}

  <table cellspacing="6" class="products products-table width-100" summary="{$lng.lbl_products_list|escape}">

    {foreach from=$products_matrix item=row name=products_matrix}

      <tr{interline name=products_matrix foreach_iteration="`$smarty.foreach.products_matrix.iteration`" foreach_total="`$smarty.foreach.products_matrix.total`" additional_class="product-name-row"}>

        {*Display product name*}
        {foreach from=$row item=product name=products}
          {if $product}

            <td{interline name=products foreach_iteration="`$smarty.foreach.products.iteration`" foreach_total="`$smarty.foreach.products.total`" additional_class="product-cell"} style="width: {$cell_width}%;">
<script type="text/javascript">
//<![CDATA[
products_data[{$product.productid}] = {ldelim}{rdelim};
//]]>
</script>
              <div class="image">
                {if $active_modules.On_Sale}
                  {include file="modules/On_Sale/on_sale_icon.tpl" product=$product module="products_t" href=$product.alt_url|default:$product.page_url|amp}
                {else}
                <a href="{$product.alt_url|default:$product.page_url|amp}">{include file="product_thumbnail.tpl" productid=$product.productid image_x=$product.tmbn_x image_y=$product.tmbn_y product=$product.product tmbn_url=$product.tmbn_url}</a>
                {/if}

                {if $active_modules.Special_Offers and $product.have_offers}
                  {include file="modules/Special_Offers/customer/product_offer_thumb.tpl"}
                {/if}
              </div>
            </td>

          {/if}
        {/foreach}

      </tr>

      {if $active_modules.Advanced_Customer_Reviews}
        {include file="modules/Advanced_Customer_Reviews/acr_products_t.tpl"}
      {/if}

      {*Display product code*}
      {if $config.Appearance.display_productcode_in_list eq "Y"}
        <tr{interline name=products_matrix foreach_iteration="`$smarty.foreach.products_matrix.iteration`" foreach_total="`$smarty.foreach.products_matrix.total`"}>

          {foreach from=$row item=product name=products}
            {if $product}

              <td{interline name=products foreach_iteration="`$smarty.foreach.products.iteration`" foreach_total="`$smarty.foreach.products.total`" additional_class="product-cell"}>
                {if $product.productcode}
                  <div class="sku">{$lng.lbl_sku}: {$product.productcode|escape}</div>
                {else}
                    &nbsp;
                {/if}
              </td>

            {/if}
          {/foreach}

        </tr>
      {/if}

      <tr{interline name=products_matrix foreach_iteration="`$smarty.foreach.products_matrix.iteration`" foreach_total="`$smarty.foreach.products_matrix.total`"}>

        {foreach from=$row item=product name=products}
          {if $product}

            <td{interline name=products foreach_iteration="`$smarty.foreach.products.iteration`" foreach_total="`$smarty.foreach.products.total`" additional_class="product-cell"}>
              <a href="{$product.alt_url|default:$product.page_url|amp}" class="product-title">{$product.product|amp}</a>
              {if $active_modules.New_Arrivals}
                {include file="modules/New_Arrivals/new_arrivals_show_date.tpl" product=$product}
              {/if} 
            </td>

          {/if}
        {/foreach}

      </tr>

      {if $active_modules.Customer_Reviews and $rating_data_exists && $featured ne "Y"}
      <tr{interline name=products_matrix foreach_iteration="`$smarty.foreach.products_matrix.iteration`" foreach_total="`$smarty.foreach.products_matrix.total`"}>

        {foreach from=$row item=product name=products}
          {if $product}

            <td{interline name=products foreach_iteration="`$smarty.foreach.products.iteration`" foreach_total="`$smarty.foreach.products.total`" additional_class="product-cell"}>

              {if $product.rating_data}
                {include file="modules/Customer_Reviews/vote_bar.tpl" rating=$product.rating_data productid=$product.productid}
              {/if}

            </td>
  
          {/if}
        {/foreach}
      </tr>
      {/if}

      <tr{interline name=products_matrix foreach_iteration="`$smarty.foreach.products_matrix.iteration`" foreach_total="`$smarty.foreach.products_matrix.total`"}>

        {*Display price/market_price/taxes*}
        {foreach from=$row item=product name=products}
          {if $product}

            <td{interline name=products foreach_iteration="`$smarty.foreach.products.iteration`" foreach_total="`$smarty.foreach.products.total`" additional_class="product-cell product-cell-price"}>
              {if $product.product_type ne "C"}

                {if $product.appearance.is_auction}

                  <span class="price">{$lng.lbl_enter_your_price}</span><br />
                  {$lng.lbl_enter_your_price_note}

                {else}

                  {if $product.appearance.has_price}

                    <div class="price-row">
                      {if $active_modules.XPayments_Subscriptions and $product.subscription}
                        {include file="modules/XPayments_Subscriptions/customer/setup_fee.tpl"}
                      {else}
                      <span class="price-value">{currency value=$product.taxed_price sup="Y"}</span>
                      <span class="market-price">{alter_currency value=$product.taxed_price}</span>
                      {/if}
                      {if $active_modules.Klarna_Payments}
                        {include file="modules/Klarna_Payments/monthly_cost.tpl" elementid="pp_conditions`$product.productid`" monthly_cost=$product.monthly_cost products_list='Y'}
                      {/if}
                      {if $active_modules.Product_Notifications ne '' and $config.Product_Notifications.prod_notif_enabled_P eq 'Y' and $config.Product_Notifications.prod_notif_show_in_list_P eq 'Y'}
                        {include file='modules/Product_Notifications/product_notification_request_button.tpl' productid=$product.productid type='P'}
                      {/if}
                    </div>
                    {if $active_modules.XPayments_Subscriptions and $product.subscription}
                      {include file="modules/XPayments_Subscriptions/customer/subscription_fee.tpl"}
                    {/if}
                    {if $active_modules.Product_Notifications ne '' and $config.Product_Notifications.prod_notif_enabled_P eq 'Y' and $config.Product_Notifications.prod_notif_show_in_list_P eq 'Y'}
                      {include file='modules/Product_Notifications/product_notification_request_form.tpl' productid=$product.productid variantid=$product.variantid|default:0 type='P'}
                    {/if}

                    {**if $product.appearance.has_market_price and $product.appearance.market_price_discount gt 0}
                      <div class="market-price">
                        {$lng.lbl_market_price}: <span class="market-price-value">{currency value=$product.list_price}</span>

                        {if $config.General.alter_currency_symbol ne ""}, {/if}
                        <span class="price-save">{$lng.lbl_save_price} {$product.appearance.market_price_discount}%</span>

                      </div>
                    {/if**}

                    {if $product.taxes}
                      <div class="taxes">{include file="customer/main/taxed_price.tpl" taxes=$product.taxes is_subtax=true}</div>
                    {/if}

                  {/if}

                  {if $active_modules.Special_Offers and $product.use_special_price}
                    {include file="modules/Special_Offers/customer/product_special_price.tpl"}
                  {/if}

                {/if}

              {elseif $product.product_type ne "C"}

                &nbsp;

              {/if}

            </td>

          {/if}
        {/foreach}

      </tr>
	{if $featured ne "Y"}
      <tr{interline name=products_matrix foreach_iteration="`$smarty.foreach.products_matrix.iteration`" foreach_total="`$smarty.foreach.products_matrix.total`"}>

        {foreach from=$row item=product name=products}
          {if $product}

            <td{interline name=products foreach_iteration="`$smarty.foreach.products.iteration`" foreach_total="`$smarty.foreach.products.total`" additional_class="product-cell product-cell-buynow"}>

                {if $active_modules.Product_Configurator and $is_pconf and $current_product}
                  {include file="modules/Product_Configurator/pconf_add_form.tpl"}
                {elseif $active_modules.Product_Configurator and $product.product_type eq "C"}
                  {assign var="url" value="product.php?productid=`$product.productid`&amp;cat=`$cat`&amp;page=`$navigation_page`"}
                  {if $featured eq 'Y'}
                    {assign var="url" value=$url|cat:"&amp;featured=Y"}
                  {/if}
                  {include file="customer/buttons/details.tpl" href=$url}
                {elseif $config.Appearance.buynow_button_enabled eq "Y" and $product.product_type ne "C"}
				
                  {if $login ne ""}
                    {include_cache file="customer/main/buy_now.tpl" product=$product cat=$cat featured=$featured is_matrix_view=$is_matrix_view login="1" smarty_get_cat=$smarty.get.cat smarty_get_page=$smarty.get.page smarty_get_quantity=$smarty.get.quantity}
                  {else}
                    {include_cache file="customer/main/buy_now.tpl" product=$product cat=$cat featured=$featured is_matrix_view=$is_matrix_view login="" smarty_get_cat=$smarty.get.cat smarty_get_page=$smarty.get.page smarty_get_quantity=$smarty.get.quantity}
                  {/if}
				
                {else}
                  &nbsp;
                {/if}

            </td>

          {/if}
        {/foreach}
      </tr>
	{/if}
      {if $active_modules.Feature_Comparison}
      <tr{interline name=products_matrix foreach_iteration="`$smarty.foreach.products_matrix.iteration`" foreach_total="`$smarty.foreach.products_matrix.total`"}>

        {*Display compare_checkbox for Feature_Comparison module*}
        {foreach from=$row item=product name=products}
          {if $product}

            <td{interline name=products foreach_iteration="`$smarty.foreach.products.iteration`" foreach_total="`$smarty.foreach.products.total`" additional_class="product-cell"}>
              {if $product.fclassid gt 0}
                <div>{include file="modules/Feature_Comparison/compare_checkbox.tpl" id=$product.productid}</div>
              {/if}
            </td>

          {/if}
        {/foreach}

      </tr>
      {/if}
      {if not $smarty.foreach.products_matrix.last}
        <tr class="separator">
          <td colspan="{$config.Appearance.products_per_row|default:1}">&nbsp;</td>
        </tr>
      {/if}

    {/foreach}

  </table>

{/if}
