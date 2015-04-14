{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, prnotice.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{*** NOTE: If you are reselling X-Cart stores, please contact us (http://www.x-cart.com) before changing the Powered-by note here. ***}
{if $main eq "catalog" and $current_category.category eq ""}
  Powered by X-Cart <a href="http://www.x-cart.com">{$sm_prnotice_txt}</a>
{else}
  Powered by X-Cart {$sm_prnotice_txt}
{/if}
