{*
6268cb1de30156d28d7266c891363c993e7b3603, v2 (xcart_4_5_5), 2012-12-12 19:25:04, quick_menu.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{if $quick_menu}

<a name="menu"></a>
{capture name=dialog}

{$lng.txt_quick_menu_text}

<br /><br />

<table cellpadding="5" cellspacing="1">

{foreach key=group item=items from=$quick_menu}
<tr>
  <td class="FormButton">{$group}:</td>
  <td>
  <select style='width: 200px;' onchange="javascript: if (this.selectedIndex != 0) self.location=this.value;">
    <option value="">[{$lng.lbl_select_target|wm_remove|escape}]</option>
{section name=id loop=$items}
{if $items[id] eq '' or $items[id].title eq ''}
    <option value="">------------------------------------</option>
{else}
    <option value="{$items[id].link}">{$items[id].title}</option>
{/if}
{/section}
  </select>
  </td>
</tr>
{/foreach}

</table>

{/capture}
{include file="dialog.tpl" title=$lng.lbl_quick_menu content=$smarty.capture.dialog extra='width="100%"'}

<br /><br />

{/if}

