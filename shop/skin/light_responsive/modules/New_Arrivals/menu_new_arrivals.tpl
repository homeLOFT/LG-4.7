{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, menu_new_arrivals.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{if $config.New_Arrivals.new_arrivals_menu eq "Y" and $is_new_arrivals_page neq true and $new_arrivals}

  {include file="customer/menu_products_thumbnails.tpl" products=$new_arrivals title=$lng.lbl_new_arrivals additional_class="menu-new_arrivals"}

{/if}
