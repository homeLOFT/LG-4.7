{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, subcategories.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{if $active_modules.Special_Offers}
  {include file="modules/Special_Offers/customer/category_offers_short_list.tpl"}
{/if}

<h1>{$current_category.category|amp}</h1>

{if $current_category.description ne ""}
  <div class="subcategory-descr">{$current_category.description|amp}</div>
  <div class="catpromo">{$lng.lbl_catpromo}</div>
{/if}

{if $categories}
  {if $config.Appearance.subcategories_per_row eq 'Y'}

    {include file="customer/main/subcategories_t.tpl"}

  {else}

    <div class="subcategory-list">
      <img class="subcategory-image" src="{get_category_image_url category=$current_category}" alt="{$current_category.category|escape}"{if $current_category.image_x} width="{$current_category.image_x}"{/if}{if $current_category.image_y} height="{$current_category.image_y}"{/if} />
      {include file="customer/main/subcategories_list.tpl"}
      <div class="clearing"></div>
    </div>

  {/if}
{/if}

{if $cat_products}

  {capture name=dialog}

    {include file="customer/main/navigation.tpl"}

    {include file="customer/main/products.tpl" products=$cat_products}

  {/capture}
  {include file="customer/dialog.tpl" title=$lng.lbl_products content="`$smarty.capture.dialog`" products_sort_url="home.php?cat=`$cat`&" sort=true additional_class="products-dialog dialog-category-products-list"}

{elseif not $cat_products and not $categories}

  {$lng.txt_no_products_in_cat}

{/if}

{if $f_products}
  {include file="customer/main/featured.tpl"}
{/if}

{if $active_modules.Bestsellers and $config.Bestsellers.bestsellers_menu ne "Y"}
  {include file="modules/Bestsellers/bestsellers.tpl"}
{/if}

{if $active_modules.New_Arrivals}
  {include file="modules/New_Arrivals/new_arrivals.tpl" new_arrivals_main="Y"}
{/if}
