{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, home_main.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{if $main eq 'cart'}
{include file="customer/main/cart.tpl"}

{elseif $checkout_step eq 0}
{include file="modules/Fast_Lane_Checkout/checkout_0_enter.tpl"}

{elseif $checkout_step eq 1}
{include file="modules/Fast_Lane_Checkout/checkout_1_profile.tpl"}

{elseif $checkout_step eq 2}
{include file="modules/Fast_Lane_Checkout/checkout_2_method.tpl"}

{elseif $checkout_step eq 3}
{include file="modules/Fast_Lane_Checkout/checkout_3_place.tpl"}

{/if}
