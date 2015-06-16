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
<input type="hidden" name="mode" value="{$mode}" />
<input type="hidden" name="list[listid]" value="{$list.listid}" />

<table cellpadding="3" cellspacing="1" width="100%">

<tr>
<td width="30%" class="FormButton">{$lng.lbl_language}:</td>
  <td width="70%">{include file="main/language_selector_short.tpl" selector_disabled=$selector_disabled script="mailchimp_news.php?mode=create&"}</td>
  <td width="10">&nbsp;</td>
</tr>

<tr>
  <td class="FormButton">List ID:</td>
  <td>{$list.mc_list_id|escape}</td>
  <td>{if $error.mc_list_id}<font class="AdminTitle">&lt;&lt;{else}&nbsp;{/if}</td>
</tr>

<tr>
  <td class="FormButton">{$lng.lbl_news_list_short_name}: </td>
  <td><input type="text" name="list[name]" value="{$list.name|escape}" size="50" style="width:90%" /></td>
  <td>{if $error.name}<font class="AdminTitle">&lt;&lt;{else}&nbsp;{/if}</td>
</tr>

<tr>
  <td class="FormButton">{$lng.lbl_list_description}: <font class="Star">*</font></td>
  <td><textarea name="list[descr]" cols="70" rows="10" style="width:90%">{$list.descr}</textarea></td>
  <td>{if $error.descr}<font class="AdminTitle">&lt;&lt;{else}&nbsp;{/if}</td>
</tr>

<tr>

<tr>
  <td>&nbsp;</td>
  <td colspan="2"><br />
  <input type="submit" value=" {$lng.lbl_save} " />
  </td>
</tr>

</table>
</form>
