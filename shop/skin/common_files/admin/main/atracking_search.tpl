{*
cdae9f6ab508ab93dc8f9934f42cdb922712d63e, v4 (xcart_4_6_2), 2013-12-25 15:45:01, atracking_search.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{if $statistics}

<script type="text/javascript">
//<![CDATA[
{literal}
function openSBox(id) {
  var obj = document.getElementById('sbox'+id);
  var fobj = document.getElementById('sbox'+id+'full');
  if (!obj || !fobj)
    return false;

  obj.style.display = 'none';
  fobj.style.display = '';

  return true;
}
{/literal}
//]]>
</script>

<form action="{$navigation_script}" name="sform" method="post">
<input type="hidden" name="submode" value="delete" />

{include file="main/navigation.tpl"}

{include file="main/check_all_row.tpl" style="line-height: 170%;" form="sform" prefix="swords"}
<table cellspacing="1" class="DataSheet">
<tr class="DataSheet">
  <th>&nbsp;</th>
  <th align="left">{$lng.lbl_search_string}</th>
  <th>{$lng.lbl_date}</th>
  <th>{$lng.lbl_count}</th>
</tr>

{foreach from=$statistics item=v key=k}
<tr>
  <td><input type="checkbox" name="swords[]" value="{$v.swordid}" /></td>
  <td width="100%">
{if $v.len gt 60}
<div id="sbox{$k}">{$v.search|truncate:60|escape:"html"} <a href="javascript:void(openSBox({$k}));">{$lng.lbl_more}</a></div>
<div id="sbox{$k}full" style="display: none;">{$v.search|escape:"html"}</div>
{else}
{$v.search|escape:"html"}
{/if}
  </td>
  <td align="center" nowrap="nowrap">{$v.date|date_format:$config.Appearance.datetime_format}</td>
  <td align="center" nowrap="nowrap">{$v.count}</td>
</tr>
{/foreach}

</table>
{include file="main/navigation.tpl"}

<br />
<input type="button" value="{$lng.lbl_delete_selected|escape}" onclick="javascript: if (checkMarks(this.form, new RegExp('^swords'))) this.form.submit();"/>
<br />
<br />
<a href="configuration.php#tr_stats_search_max_period">{$lng.txt_change_settings}</a>

</form>

{else}

<br />
<div align="center">{$lng.txt_no_statistics}</div>
<a href="configuration.php#tr_stats_search_max_period">{$lng.txt_change_settings}</a>

{/if}

