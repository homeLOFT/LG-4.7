{*
5eba2fb2ff75df4a18d07913b095c0b884b1e772, v2 (xcart_4_6_4), 2014-06-25 11:08:30, card_list_admin.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}

{if $saved_cards}
    <table cellspacing="3" cellpadding="2" border="0" width="70%" class="saved-cards">
      <tr>
        <th>{$lng.lbl_order}</th>
        <th>{$lng.lbl_saved_card_header}</th>
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
          <td>
            <a href="user_modify.php?action=delete_saved_card&id={$card_id}&user={$smarty.get.user}&usertype=C">{$lng.lbl_remove}</a>
          </td>
        </tr>
      {/foreach}
    </table>
{/if}
