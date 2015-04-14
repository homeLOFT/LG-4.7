{*
77c5c46814ed430684d3e2145cd8915fc8b7c81c, v1 (xcart_4_6_1), 2013-08-26 17:55:46, payment_recharge.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}

{$lng.lbl_saved_cards_payment_note}

<br /><br />

{if $saved_cards}

  {include file="modules/XPayments_Connector/card_list_customer_checkout.tpl"}

{/if}
