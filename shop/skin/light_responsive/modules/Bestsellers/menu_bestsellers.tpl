{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, menu_bestsellers.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{if $config.Bestsellers.bestsellers_menu eq "Y"}
  {getvar var=bestsellers func=func_tpl_get_bestsellers}
  {if $bestsellers}

    {include file="customer/menu_products_thumbnails.tpl" products=$bestsellers title=$lng.lbl_bestsellers additional_class="menu-bestsellers" module="bestsellers"}

  {/if}
{/if}
