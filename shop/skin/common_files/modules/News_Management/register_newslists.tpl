{* 850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, register_newslists.tpl, joy *}
{if $active_modules.News_Management and $newslists}

{if $hide_header eq ""}
<tr>
<td height="20" colspan="3"><b>{$lng.lbl_newsletter}</b><hr size="1" noshade="noshade" /></td>
</tr>
{/if}

<tr>
<td colspan="3">{$lng.lbl_newsletter_signup_text}</td>
</tr>

<tr>
<td colspan="2">&nbsp;</td>
<td>
<table border="0">

{section name=idx loop=$newslists}
{assign var="listid" value=$newslists[idx].listid}
<tr>
<td><input type="checkbox" name="subscription[{$listid}]" {if $subscription[$listid] ne ""}checked="checked"{/if} /></td>
<td>{$newslists[idx].name}</td>
</tr>
<tr>
<td>&nbsp;</td>
<td><i>{$newslists[idx].descr}</i></td>
</tr>
{/section}

</table>
</td>
</tr>

{/if}
