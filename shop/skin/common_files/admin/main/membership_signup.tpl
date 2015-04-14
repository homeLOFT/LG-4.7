{*
140b0a4c5a62f786dabb0a6cb27cfcb15e4ab005, v3 (xcart_4_5_3), 2012-10-05 07:28:48, membership_signup.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
<tr valign="middle">
<td align="right">{$lng.lbl_signup_for_membership}</td>
<td></td>
<td nowrap="nowrap">
<select name="pending_membershipid">
<option value="0">{$lng.lbl_not_member}</option>
{foreach from=$membership_levels item=v}
<option value="{$v.membershipid}"{if $userinfo.pending_membershipid eq $v.membershipid} selected="selected"{/if}>{$v.membership}</option>
{/foreach}
</select>
</td>
</tr>
