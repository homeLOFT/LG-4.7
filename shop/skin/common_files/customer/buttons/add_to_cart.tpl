{*
48ce763c41afc592c58ff57c35ce90c1b45d157a, v2 (xcart_4_5_1), 2012-05-25 07:23:46, add_to_cart.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{* Uncomment this line if you don't want buy more button behavior: 
{include file="customer/buttons/button.tpl" button_title=$lng.lbl_add_to_cart additional_button_class=$additional_button_class|cat:' add-to-cart-button'}
*}
{* Comment the following 5 lines if you don't want buy more button behavior: *} 
{if $product.appearance.added_to_cart} 
  {include file="customer/buttons/button.tpl" button_title=$lng.lbl_add_more additional_button_class=$additional_button_class|cat:' add-to-cart-button added-to-cart-button'}
{else} 
  {include file="customer/buttons/button.tpl" button_title=$lng.lbl_add_to_cart additional_button_class=$additional_button_class|cat:' add-to-cart-button'}
{/if} 
