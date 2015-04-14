{*
44bfc83815ad5e5c06a58f653cbba8c5f1b89b87, v2 (xcart_4_7_0), 2015-03-02 13:29:05, content.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{getvar var=recently_viewed_products func=func_tpl_get_recently_viewed_products}
{if $recently_viewed_products}

  {include file="customer/menu_products_links.tpl" products=$recently_viewed_products title=$lng.rviewed_section additional_class="menu-rviewed-section" module="recently_viewed"}

{/if}
