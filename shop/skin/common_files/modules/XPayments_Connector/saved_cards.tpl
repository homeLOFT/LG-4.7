{*
f7f946996b3f74495f07af7fc9812865f7e93875, v2 (xcart_4_6_5), 2014-08-04 14:12:36, saved_cards.tpl, aim 
vim: set ts=2 sw=2 sts=2 et:
*}

<h1>{$lng.lbl_saved_cards}</h1>

{$lng.lbl_saved_cards_top_note}

<br /><br />

{if $saved_cards}

  {include file="modules/XPayments_Connector/card_list_customer.tpl"}

{else}

  {$lng.lbl_no_saved_cards}

  {if $allow_add_new_card}
    <br /><br />
    {include file="customer/buttons/button.tpl" button_title=$lng.lbl_saved_card_add_new href="javascript: showXPCFrame(this);"}
    {include file="modules/XPayments_Connector/saved_cards_add_new.tpl"}
  {/if}

{/if}
