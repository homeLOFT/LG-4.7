{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, product_event_cart_contents.tpl, joy 
vim: set ts=2 sw=2 sts=2 et:
*}
{if $product.event_data ne ""}
{assign var=creator value="`$product.event_data.creator_title` `$product.event_data.firstname` `$product.event_data.lastname`"}
<div class="event-info">
  {$lng.lbl_giftreg_present_for|substitute:"event_name":$product.event_data.title:"eventid":$product.event_data.event_id:"creator":$creator}
</div>
{/if}
