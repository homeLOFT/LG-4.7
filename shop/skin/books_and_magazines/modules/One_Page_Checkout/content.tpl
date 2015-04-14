{*
12fa2df57dc48f026c73ec5ba1e8ae0a62f9f935, v7 (xcart_4_6_5), 2014-08-06 11:57:14, content.tpl, mixon
vim: set ts=2 sw=2 sts=2 et:
*}
{if $top_message}
  {include file="main/top_message.tpl"}
{/if}

{if $main eq 'cart'}

<table width="100%" cellspacing="0" cellpadding="0">
<tr>
	<td><h1>{$lng.lbl_your_shopping_cart}</h1></td>

	<td align="right">
  <div class="checkout-buttons">
    {if $active_modules.POS_System ne "" && $user_is_pos_operator eq "Y"}
      {include file="modules/POS_System/process_order_button.tpl" position="top"}
    {else}
    {if !$std_checkout_disabled and !$amazon_enabled and !$paypal_express_active}
      {include file="customer/buttons/button.tpl" button_title=$lng.lbl_checkout style="div_button" href="cart.php?mode=checkout" additional_button_class="checkout-3-button main-button"}
    {/if}
    {include file="customer/buttons/button.tpl" button_title=$lng.lbl_continue_shopping style="div_button" href=$stored_navigation_script additional_button_class="checkout-1-button"}
    {/if}
  </div>
  <div class="clearing"></div>
	</td>
</tr>
</table>

  {include file="customer/main/cart.tpl"}

{else}

  {include file="modules/One_Page_Checkout/opc_main.tpl"}

{/if}
