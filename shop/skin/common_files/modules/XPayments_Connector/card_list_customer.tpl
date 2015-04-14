{*
163da91a21837e50d82e542d62cde62d0cfe7931, v4 (xcart_4_6_4), 2014-06-28 11:37:10, card_list_customer.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}

<form action="saved_cards.php?mode=set_default" method="post">
  <table cellspacing="3" cellpadding="2" border="0" width="90%" class="saved-cards">
    {if $saved_cards}
      <tr>
        <th>{$lng.lbl_order}</th>
        <th>{$lng.lbl_saved_card_header}</th>
        <th class="default-column">{$lng.lbl_saved_card_default}</th>
        <th></th>
      </tr>
      {foreach from=$saved_cards item=card key=card_id}
        <tr>
          <td>
            {if $card.orderid}
              <a href="order.php?orderid={$card.orderid}">#{$card.orderid}</a>
            {else}
              {$lng.txt_not_available}
            {/if}
          </td>
          <td>
            <div class="card-icon-container">
              <span class="card {$card.card_type|lower}"><img src="{$ImagesDir}/spacer.gif" alt="{$card.card_type}"/></span>
            </div>
            <div class="number">{$card.card_num}</div>
          </td>
          <td class="default-column">
            <input type="radio" name="id" {if $card_id eq $default_card_id}} checked="checked"{/if} value="{$card_id}">
          </td>
          <td>
            <a href="saved_cards.php?mode=delete&id={$card_id}">{$lng.lbl_remove}</a>
          </td>
        </tr>
      {/foreach}
    {/if}  
    <tr class="button-row">
      <td colspan="4">
        {include file="customer/buttons/button.tpl" button_title=$lng.lbl_saved_card_set_default_card type="input" additional_button_class="main-button"}
        {if $allow_add_new_card}
          &nbsp;&nbsp;&nbsp;&nbsp;
          {include file="customer/buttons/button.tpl" button_title=$lng.lbl_saved_card_add_new href="javascript: showXPCFrame(this);"}
        {/if}
      </td>
    </tr>
  </table>
</form>

{if $allow_add_new_card}
  <br /><br />
  {include file="modules/XPayments_Connector/saved_cards_add_new.tpl"}
{/if}
