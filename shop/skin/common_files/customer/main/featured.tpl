{*
68886de1ec4e849bec46db42823fde475423afc3, v2 (xcart_4_6_1), 2013-08-05 15:44:23, featured.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{if $f_products ne ""}
  {capture name=dialog}
    {include file="customer/main/products.tpl" products=$f_products featured="Y"}
  {/capture}
  {include file="customer/dialog.tpl" title=$lng.lbl_featured_products content=$smarty.capture.dialog sort=false additional_class="products-dialog dialog-featured-list" sort=false}
{/if}
