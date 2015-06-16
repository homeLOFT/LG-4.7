{*
$Id$
vim: set ts=2 sw=2 sts=2 et:
*}
{if $mode eq "modify"}
{assign var="selector_disabled" value="1"}
{else}
{assign var="selector_disabled" value="0"}
{/if}

<form action="mailchimp_news.php" method="post">
<input type="hidden" name="mode" value="import" />
<input type="hidden" name="list[listid]" value="{$list.listid}" />

<table cellpadding="3" cellspacing="1" width="100%">
<tr>
  <td>Import mailing lists from Mailchimp </td>
  <td colspan="2"><br />
  <input type="submit" value=" {$lng.lbl_import} " />
  </td>
</tr>

</table>
</form>
