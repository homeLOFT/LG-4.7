{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, menu_products_thumbnails.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{if $products}
  {capture name=menu}

    <ul>
    {foreach from=$products item=p name=products}

      {assign var="url" value="product.php?productid=`$p.productid`"}
      {if $module eq "bestsellers"}
        {assign var="url" value=$url|cat:"&amp;cat=`$cat`&amp;bestseller=Y"}
      {/if}

      <li{interline name=products foreach_iteration="`$smarty.foreach.products.iteration`" foreach_total="`$smarty.foreach.products.total`"}>
        <div class="product-photo">
          {if $active_modules.On_Sale and $module ne ""}
            {include file="modules/On_Sale/on_sale_icon.tpl" product=$p current_skin=$current_skin module=$module href=$url}
          {else}
            <a href="{$url}">{include file="product_thumbnail.tpl" tmbn_url=$p.tmbn_url productid=$b.productid image_x=$p.tmbn_x class="image" product=$p.product}</a>
          {/if}
        </div>
        <div class="details">
          <a class="product-title" href="{$url}">{$p.product|amp}</a>
          <div class="price-row">
            <span class="price-value">{currency value=$p.taxed_price}</span>
            <span class="market-price">{alter_currency value=$p.taxed_price}</span>
          </div>
        </div>
        <div class="clearing"></div>
      </li>
    {/foreach}
    </ul>

  {/capture}
  {include file="customer/menu_dialog.tpl" title=$title content=$smarty.capture.menu additional_class="menu-products menu-products-thumbnails `$additional_class`"}
{/if}
