{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, condition_membership.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
<table width="100%" cellspacing="3" cellpadding="0" border="0">
{assign var="ex_flag" value=1}
{foreach from=$condition.memberships item=membership}
{if $membership.selected}
{assign var="ex_flag" value=0}
<tr>
  <td>{$membership.name|escape}</td>
</tr>
{/if}
{/foreach}
{if $ex_flag}
<tr>
  <td><i>{$lng.txt_no_memberships_defined}</i></td>
</tr>
{/if}
</table>
