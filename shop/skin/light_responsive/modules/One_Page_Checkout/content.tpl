{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, content.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{if $top_message}
  {include file="main/top_message.tpl"}
{/if}

{if $main eq 'cart'}

  <div class="checkout-buttons">
    {if $active_modules.POS_System ne "" && $user_is_pos_operator eq "Y"}
      {include file="modules/POS_System/process_order_button.tpl" position="top"}
    {else}
    {include file="customer/buttons/button.tpl" button_title=$lng.lbl_continue_shopping style="div_button" href=$stored_navigation_script additional_button_class="checkout-1-button"}
    {if !$std_checkout_disabled and !$amazon_enabled and !$paypal_express_active}
      {include file="customer/buttons/button.tpl" button_title=$lng.lbl_checkout style="div_button" href="cart.php?mode=checkout" additional_button_class="checkout-3-button"}
    {/if}
    {/if}
  </div>
  <div class="clearing"></div>

  {include file="customer/main/cart.tpl"}

{else}

  {include file="modules/One_Page_Checkout/opc_main.tpl"}

{/if}
<div class="clearing"></div>

<div class="opc_term_display">
{$lng.lbl_cart_terms}
</div>
