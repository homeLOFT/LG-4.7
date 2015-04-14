{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, product.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{include file="form_validation_js.tpl"}

{if $config.Appearance.display_np_products eq 'Y'}
  {include file="customer/main/np_products.tpl"}
{/if}

<h1>{$product.producttitle|amp}</h1>

{if $product.product_type eq "C" and $active_modules.Product_Configurator}

  {include file="modules/Product_Configurator/pconf_customer_product.tpl"}

{else}

  {if $config.General.ajax_add2cart eq 'Y' and $config.General.redirect_to_cart ne 'Y' and not ($smarty.cookies.robot eq 'X-Cart Catalog Generator' and $smarty.cookies.is_robot eq 'Y')}
    {include file="customer/ajax.add2cart.tpl" _include_once=1}

<script type="text/javascript">
//<![CDATA[
{literal}
$(ajax).bind(
  'load',
  function() {
    var elm = $('.product-details').get(0);
    return elm && ajax.widgets.product(elm);
  }
);
{/literal}
//]]>
</script>

  {/if}

  {capture name=dialog}

    <div class="product-details">

      <div class="image"{if $max_image_width gt 0} style="width: {$max_image_width}px;"{/if}>

        {if $active_modules.Detailed_Product_Images and $config.Detailed_Product_Images.det_image_popup eq 'Y' and $images ne ''}

          {include file="modules/Detailed_Product_Images/widget.tpl"}

        {else}

          <div class="image-box">
            {if $active_modules.On_Sale}
              {include file="modules/On_Sale/on_sale_icon.tpl" product=$product module="product"}
            {else}
            {include file="product_thumbnail.tpl" productid=$product.image_id image_x=$product.image_x image_y=$product.image_y product=$product.product tmbn_url=$product.image_url id="product_thumbnail" type=$product.image_type}
            {/if}
          </div>

        {/if}

        {if $active_modules.Magnifier and $config.Magnifier.magnifier_image_popup eq 'Y' and $zoomer_images}
          {include file="modules/Magnifier/popup_magnifier.tpl"}
        {/if}

{if $product.forsale ne "B"}

      <ul class="simple-list">
      {if $active_modules.XAuth}
        {include file="modules/XAuth/rpx_ss_product.tpl"}
      {/if}

      {if $active_modules.Socialize}
      <li>
        {include file="modules/Socialize/buttons_row.tpl" detailed=true href="`$current_location`/`$canonical_url`"}
      </li>
      {/if}

      {if $config.Company.support_department neq ""} 
      <li>
      <div class="ask-question">
        {include file="customer/buttons/button.tpl" button_title=$lng.lbl_ask_question_about_product style="link" href="javascript: return !popupOpen(xcart_web_dir + '/popup_ask.php?productid=`$product.productid`')"}
      </div>

      <div class="clearing"></div>
      </li>
      {/if}

      </ul>

    {/if}
    
      </div>

      <div class="details">
        {include file="customer/main/product_details.tpl"}
      </div>

    </div>
    <div class="clearing"></div>

{/capture}
{* WCM - Dynamic Product Tabs *}
{if $active_modules.WCM_Dynamic_Product_Tabs AND $config.WCM_Dynamic_Product_Tabs.tab_product_details eq 'Y'}
     {include file="modules/DynamicProductTabs/dynamictabs.tpl" content=$smarty.capture.dialog noborder=true}
{else}
     {include file="customer/dialog.tpl" title=$product.producttitle content=$smarty.capture.dialog noborder=true}
{/if}
{* /WCM - Dynamic Product Tabs *}
{/if}

{* WCM - Dynamic Product Tabs *}
{if $active_modules.WCM_Dynamic_Product_Tabs AND $config.WCM_Dynamic_Product_Tabs.tab_product_details ne 'Y'}
     {include file="modules/DynamicProductTabs/dynamictabs.tpl"}
{/if}
{if !$active_modules.WCM_Dynamic_Product_Tabs}
{if $product_tabs}
     {if $show_as_tabs}
          {include file="customer/main/ui_tabs.tpl" prefix="product-tabs-" mode="inline" tabs=$product_tabs}
     {else}
          {foreach from=$product_tabs item=tab key=ind}
               {include file=$tab.tpl}
          {/foreach}
     {/if}
{/if}
{/if} 
{* / WCM - Dynamic Product Tabs *}

{if $active_modules.Product_Options and ($product_options ne '' or $product_wholesale ne '') and ($product.product_type ne "C" or not $active_modules.Product_Configurator)}
<script type="text/javascript">
//<![CDATA[
check_options();
//]]>
</script>
{/if}
