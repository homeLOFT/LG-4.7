{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, new_offers_message.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{if $new_offers_message.content}
  {$new_offers_message.content}
{else}
  {xoffers_promo mode="random"}
{/if}
