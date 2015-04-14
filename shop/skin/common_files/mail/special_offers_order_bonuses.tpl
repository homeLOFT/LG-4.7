{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, special_offers_order_bonuses.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{if $bonuses.points ne 0 or $bonuses.memberships ne ""}
{$lng.lbl_sp_order_bonuses}:
---------
{if $bonuses.points ne 0}
{$lng.lbl_sp_customer_bonus_points|mail_truncate}{$bonuses.points}
({$lng.lbl_sp_earned_bonus_points_explanation})
{/if}
{if $bonuses.memberships ne ""}
{$lng.lbl_sp_customer_bonus_memberships|mail_truncate}{foreach name=memberships from=$bonuses.memberships item=membership}{$membership}{if $smarty.foreach.memberships.last ne "1"}, {/if}{/foreach}
{/if}


{/if}
