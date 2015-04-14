{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, welcome.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
<div class="welcome-table">

  {if $active_modules.Bestsellers}
    {getvar var=bestsellers func=func_tpl_get_bestsellers}
  {/if}

  {if $active_modules.Bestsellers && $bestsellers}
    <div class="bestsellers-cell">
      {include file="modules/Bestsellers/menu_bestsellers.tpl"}
    </div>
  {/if}

  <div class="welcome-cell{if $active_modules.Bestsellers and $bestsellers and $config.Bestsellers.bestsellers_menu eq 'Y'} with-bestsellers{/if}">

    {include file="customer/main/home_page_banner.tpl"}

    {$lng.txt_welcome}

    {if $active_modules.Bestsellers and $config.Bestsellers.bestsellers_menu ne "Y"}
      {include file="modules/Bestsellers/bestsellers.tpl"}
    {/if}

    {if $active_modules.New_Arrivals}
      {include file="modules/New_Arrivals/new_arrivals.tpl" is_home_page="Y"}
    {/if}

    {if $active_modules.Refine_Filters}
      {include file="modules/Refine_Filters/home_products.tpl"}
    {/if}

    {if $active_modules.On_Sale}
      {include file="modules/On_Sale/on_sale.tpl" is_home_page="Y"}
    {/if}

    {include file="customer/main/featured.tpl"}

  </div>

</div>
<div class="clearing"></div>
