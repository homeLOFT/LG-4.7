{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, new_offers_short_list.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{if $new_offers}
  {include file="modules/Special_Offers/customer/offers_short_list.tpl" offers_list=$new_offers generic_message=$lng.lbl_sp_new_offers_generic link_href="offers.php"}
{/if}
