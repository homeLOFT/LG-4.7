{*
8ec8b858917c921a726e2197071ce74d020e5ead, v1 (xcart_4_6_0), 2013-04-02 16:43:59, featured.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{if $f_products ne ""}
  {capture name=dialog}
    {include file="customer/main/products.tpl" products=$f_products featured="Y"}
  {/capture}
  {include file="customer/dialog.tpl" title=$lng.lbl_featured_products content=$smarty.capture.dialog sort=false additional_class="products-dialog dialog-featured-list" row_length=$row_length}
{/if}
