{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, cart_checkout_buttons.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{if $cart.not_used_free_products}

  <div class="offers-cart-button">
    {include file="customer/buttons/button.tpl" button_title=$lng.lbl_sp_add_free_products href="offers.php?mode=add_free" style="link"}
  </div>

{elseif $customer_unused_offers}

  <div class="offers-cart-button">
    {include file="customer/buttons/button.tpl" button_title=$lng.lbl_sp_unused_offers href="offers.php?mode=unused" style="link"}
  </div>

{/if}
