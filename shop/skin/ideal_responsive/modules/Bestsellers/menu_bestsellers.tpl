{*
16fd72c950254a8d4861815dac0d6147af93e43f, v7 (xcart_4_7_0), 2014-12-23 10:13:26, menu_bestsellers.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{if $config.Bestsellers.bestsellers_menu eq "Y"}
{getvar var=bestsellers func=func_tpl_get_bestsellers}
{if $bestsellers}


  {capture name=menu}
    <ul>

      {foreach from=$bestsellers item=b name=bestsellers}
        <li{interline name=bestsellers foreach_iteration="`$smarty.foreach.bestsellers.iteration`" foreach_total="`$smarty.foreach.bestsellers.total`"}>
			<div class="image">
          {if $active_modules.On_Sale}
            {include file="modules/On_Sale/on_sale_icon.tpl" product=$b current_skin="ideal_responsive" module="bestsellers" href="product.php?productid=`$b.productid`&amp;cat=`$cat`&amp;bestseller=Y"}
          {else}
          <a href="product.php?productid={$b.productid}&amp;cat={$cat}&amp;bestseller=Y">{include file="product_thumbnail.tpl" tmbn_url=$b.tmbn_url productid=$b.productid image_x=$b.tmbn_x class="image" product=$b.product}</a>
          {/if}
			</div>
			<a href="product.php?productid={$b.productid}&amp;cat={$cat}&amp;bestseller=Y">{$b.product|amp}</a>
			<div class="price-row">
				<span class="price-value">{currency value=$b.taxed_price}</span>
				<span class="market-price">{alter_currency value=$b.taxed_price}</span>
			</div>
        </li>
      {/foreach}

    </ul>
  {/capture}
  {include file="customer/menu_dialog.tpl" title=$lng.lbl_bestsellers content=$smarty.capture.menu additional_class="menu-bestsellers"}

{/if}
{/if}
