{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, offers.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{if $mode eq "add_free"}

  {include file="modules/Special_Offers/customer/checkout_free_products.tpl"}

{else}

  {include file="modules/Special_Offers/customer/offers_list.tpl"}

{/if}
