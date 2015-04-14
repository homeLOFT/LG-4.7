{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, menu_products_links.tpl, aim

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
        <a class="product-title" href="{$url}">{$p.product|amp}</a>
      </li>
    {/foreach}
    </ul>

  {/capture}
  {include file="customer/menu_dialog.tpl" title=$title content=$smarty.capture.menu additional_class="menu-products menu-products-links `$additional_class`"}
{/if}
