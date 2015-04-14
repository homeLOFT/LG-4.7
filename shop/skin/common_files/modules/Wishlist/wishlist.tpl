{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, wishlist.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}

<h1>{$lng.lbl_wish_list}</h1>

{capture name=dialog}

  {include file="modules/Wishlist/wl_products.tpl"}

{/capture}
{include file="customer/dialog.tpl" title=$lng.lbl_wish_list content=$smarty.capture.dialog noborder=true}

{if $active_modules.Gift_Registry}
  {include file="modules/Gift_Registry/events_list.tpl" is_internal=true}
{/if}
