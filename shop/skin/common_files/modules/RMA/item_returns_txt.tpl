{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, item_returns_txt.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{if $product.returns_sum_R gt 0 or $product.returns_sum_A gt 0 or $product.returns_sum_C gt 0}
{if $usertype ne 'P' or $active_modules.Simple_Mode}
<a href="returns.php?mode=search&amp;search[itemid]={$product.itemid}">{$lng.lbl_returns}</a>:
{else}
{$lng.lbl_returns}:
{/if}
{if $product.returns_sum_C eq $product.amount}{$lng.lbl_product_returned}
{else}
{if $product.returns_sum_R gt 0}{$lng.lbl_products_requested|substitute:"N":$product.returns_sum_R}<br />{/if}
{if $product.returns_sum_A gt 0}{$lng.lbl_products_authorized|substitute:"N":$product.returns_sum_A}<br />{/if}
{if $product.returns_sum_C gt 0}{$lng.lbl_products_returned|substitute:"N":$product.returns_sum_C}<br />{/if}
{/if}
{if $product.returned_to_stock gt 0}
&nbsp;({$lng.lbl_products_returned_stock|substitute:"N":$product.returned_to_stock})
{/if}
{/if}

