{*
163da91a21837e50d82e542d62cde62d0cfe7931, v3 (xcart_4_6_4), 2014-06-28 11:37:10, card_list_customer_checkout.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}

{if $saved_cards}
  <ul class="saved-cards">
    <li class="default-item">
      <label>
        <input type="hidden" name="saved_card_id" value="{$default_card_id}" id="default_card"/>
        <span class="card-icon-container">
          <span class="card {$saved_cards[$default_card_id].card_type|lower}"></span>
        </span>
        <span class="number">{$saved_cards[$default_card_id].card_num}</span>
      </label>  
    </li>
    {foreach from=$saved_cards item=card key=card_id}
      <li class="all-items" style="display: none">
        <label>
          <input type="radio" name="saved_card_id" value="{$card_id}"{if $card_id eq $default_card_id} checked="checked"{/if}/>
          <span class="card-icon-container">
            <span class="card {$card.card_type|lower}"></span>
            <img src="{$ImagesDir}/spacer.gif" alt="{$card.card_type}" width="0" height="0"/>
          </span>
          <span class="number">{$card.card_num}</span>
        </label>
      </li>
    {/foreach}
  </ul>
  &nbsp;&nbsp;&nbsp;&nbsp;
  <small><a class="default-item" href="javascript: void(0);" onclick="javascript: switchSavedCards();">{$lng.lbl_show_all_cards}</a></small>
{/if}

<script type="text/javascript">
{literal}

    function switchSavedCards() {
        $('.default-item').hide();
        $('.all-items').show();
        $('#default_card').remove();
    }

{/literal}
</script>
