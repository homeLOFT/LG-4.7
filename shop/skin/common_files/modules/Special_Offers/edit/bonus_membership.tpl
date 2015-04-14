{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, bonus_membership.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{if $bonus.bonus_data eq ""}
{$lng.txt_sp_empty_params_bonus_generic_edit}
<br /><br />
{/if}
<select name="bonus[{$bonus.bonus_type}][memberships][]" size="5" multiple="multiple">
{foreach from=$bonus.memberships item=membership key=membershipid}
  <option value="{$membershipid}"{if $membership.selected} selected="selected"{/if}>{$membership.name|escape}</option>
{/foreach}
</select>
<br />
<input type="submit" value=" {$lng.lbl_update} " />
