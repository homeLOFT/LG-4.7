{*
326917223b194fd8e4b13d13595b6689cda0a3a4, v1 (xcart_4_7_0), 2015-03-03 09:57:19, content.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{if $top_message}
  {include file="main/top_message.tpl"}
{/if}

{if $main ne 'cart'}

  {include file="modules/Fast_Lane_Checkout/tabs_menu.tpl"}
  <div class="clearing"></div>

{else}

  <div class="checkout-buttons">
    {include file="customer/buttons/button.tpl" button_title=$lng.lbl_continue_shopping style="div_button" href=$stored_navigation_script additional_button_class="checkout-1-button"}
    {if !$std_checkout_disabled and !$amazon_enabled and !$paypal_express_active}
      {include file="customer/buttons/button.tpl" button_title=$lng.lbl_checkout style="div_button" href="cart.php?mode=checkout" additional_button_class="checkout-3-button"}
    {/if}
  </div>
  <div class="clearing"></div>

{/if}

{include file="modules/Fast_Lane_Checkout/home_main.tpl"}
