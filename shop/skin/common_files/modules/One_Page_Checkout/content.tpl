{*
d19580b5f5f5d901a1315c71b3a6eada0239c273, v8 (xcart_4_6_4), 2014-04-07 17:45:38, content.tpl, aim
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
    {if !$std_checkout_disabled and !$amazon_enabled and !$paypal_express_active}
      {include file="customer/buttons/button.tpl" button_title=$lng.lbl_checkout style="div_button" href="cart.php?mode=checkout" additional_button_class="checkout-3-button"}
    {/if}
    {include file="customer/buttons/button.tpl" button_title=$lng.lbl_continue_shopping style="div_button" href=$stored_navigation_script additional_button_class="checkout-1-button"}
    {/if}
  </div>
  <div class="clearing"></div>

  {include file="customer/main/cart.tpl"}

{else}

  {include file="modules/One_Page_Checkout/opc_main.tpl"}

{/if}
