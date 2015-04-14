{*
f7f946996b3f74495f07af7fc9812865f7e93875, v2 (xcart_4_6_5), 2014-08-04 14:12:36, saved_cards_admin.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{if $hide_header eq ""}
<tr>
<td colspan="3" class="RegSectionTitle">{$lng.lbl_saved_cards}<hr size="1" noshade="noshade" /></td>
</tr>
{/if}

<tr>
  <td colspan="3">

    {if $saved_cards}

      {include file="modules/XPayments_Connector/card_list_admin.tpl"}

    {else}

      {$lng.lbl_no_saved_cards}

    {/if}

  </td>
</tr>
