{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, order_bonuses.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
<tr>
  <td colspan="2">{include file="main/subheader.tpl" title=$lng.lbl_sp_order_bonuses}</td>
</tr>

{if $bonuses.points ne 0}
<tr valign="top"> 
  <td>&nbsp;&nbsp;{$lng.lbl_sp_customer_bonus_points}</td>
  <td>{$bonuses.points}</td>
</tr>
{/if}

{if $bonuses.memberships ne ""}
<tr valign="top"> 
  <td>&nbsp;&nbsp;{$lng.lbl_sp_customer_bonus_memberships}</td>
  <td>
{foreach name=memberships from=$bonuses.memberships item=membership}
{$membership}{if $smarty.foreach.memberships.last ne "1"}, {/if}
{/foreach}
  </td>
</tr>
{/if}

<tr>
  <td colspan="2" height="10">&nbsp;</td>
</tr>
