{*
5eba2fb2ff75df4a18d07913b095c0b884b1e772, v2 (xcart_4_6_4), 2014-06-25 11:08:30, card_list_admin_recharge.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}

{if $saved_cards}
  <ul class="saved-cards">
    {foreach from=$saved_cards item=card key=card_id}
      <li>
        <label>
          <input type="radio" name="saved_card_id" value="{$card_id}"{if $card.is_default} checked="checked"{/if}/>
          <span class="card-icon-container">
            <span class="card {$card.card_type|lower}"><img src="{$ImagesDir}/spacer.gif" alt="{$card.card_type}"/></span>
          </span>
          <span class="number">{$card.card_num}</span>
        </label>
      </li>
    {/foreach}
  </ul>
{/if}
