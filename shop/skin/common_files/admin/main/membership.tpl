{*
38433f8ab6f8009ea4c652b1f214c9e118d7960c, v2 (xcart_4_5_1), 2012-06-07 12:33:51, membership.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
<tr valign="middle">
<td align="right">{$lng.lbl_membership}</td>
<td></td>
<td nowrap="nowrap">
<select name="membershipid">
<option value="0">{$lng.lbl_not_member}</option>
{foreach from=$membership_levels item=v}
<option value="{$v.membershipid}"{if $userinfo.membershipid eq $v.membershipid} selected="selected"{/if}>{$v.membership}</option>
{/foreach}
</select>
</td>
</tr>
