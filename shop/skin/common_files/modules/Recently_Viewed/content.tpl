{*
04fde8f686ed0be7315b88835142e1a07fc30925, v15 (xcart_4_6_2), 2014-01-04 06:59:41, content.tpl, random 
vim: set ts=2 sw=2 sts=2 et:
*}
{getvar var=recently_viewed_products func=func_tpl_get_recently_viewed_products}
{if $recently_viewed_products}
  {capture name=menu}
    {section name=i loop=$recently_viewed_products}
      {assign var="url" value="product.php?productid=`$recently_viewed_products[i].productid`"}
      <div class="item">
        <div class="image">
            {if $active_modules.On_Sale}
              {include file="modules/On_Sale/on_sale_icon.tpl" product=$recently_viewed_products[i] module="recently_viewed" href=$url}
            {else}
            <a href="{$url}">{include file="product_thumbnail.tpl" productid=$recently_viewed_products[i].productid image_x=$recently_viewed_products[i].image_x product=$recently_viewed_products[i].product tmbn_url=$recently_viewed_products[i].tmbn_url}</a>
            {/if}
        </div>
        <a href="{$url}" class="product-title">{$recently_viewed_products[i].product|amp}</a>
        {if $active_modules.XPayments_Subscriptions and $recently_viewed_products[i].subscription}
          {include file="modules/XPayments_Subscriptions/customer/simple_products_list.tpl" product=$recently_viewed_products[i]}
        {else}
        <div class="price-row">
          <span class="price-value">{currency value=$recently_viewed_products[i].taxed_price}</span>
          <span class="market-price">{alter_currency value=$recently_viewed_products[i].taxed_price}</span>
        </div>
        {/if}
        {if not $smarty.section.i.last}
          <img src="{$ImagesDir}/spacer.gif" class="separator" alt="" />
        {/if}
      </div>
    {/section}
  {/capture}
  {include file="customer/menu_dialog.tpl" title=$lng.rviewed_section content=$smarty.capture.menu additional_class="menu-rviewed-section"}
{/if}
