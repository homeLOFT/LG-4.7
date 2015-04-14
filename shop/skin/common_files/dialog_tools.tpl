{*
c4dbad033b074a0552eb94cab41b849d29614cb6, v13 (xcart_4_6_1), 2013-08-20 15:21:06, dialog_tools.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{if $dialog_tools_data}
  {assign var="left" value=$dialog_tools_data.left}
  {assign var="right" value=$dialog_tools_data.right}
{/if}

{if $dialog_tools_data.left
    or $dialog_tools_data.right
    or $dialog_tools_data.help and $config.Appearance.enable_admin_context_help eq 'Y'
}
<table cellpadding="0" cellspacing="0" width="100%" class="dialog-tools-table">
<tr>
  <td>

  <div class="dialog-tools">

      <ul class="dialog-tools-header">
{if $left}
        <li class="dialog-header-left{if $dialog_tools_data.show eq "right"} dialog-tools-nonactive{/if}" onclick="javascript: dialog_tools_activate('left', 'right');">
        {if $left.title}{$left.title}{else}{$lng.lbl_in_this_section}{/if}
        </li>
{/if}
{if $right}
        <li class="dialog-header-right{if $left and $dialog_tools_data.show ne "right"} dialog-tools-nonactive{/if}" onclick="javascript: dialog_tools_activate('right', 'left');">
        {if $right.title}{$right.title}{else}{$lng.lbl_see_also}{/if}
        </li>
{/if}
      </ul>

    <div class="clearing">&nbsp;</div>

    <div class="dialog-tools-box">

{if $left}
{if $left.data}
{assign var=left value=$left.data}
{/if}
      <ul class="dialog-tools-content dialog-tools-left{if $dialog_tools_data.show eq "right"} hidden{/if}">
{foreach from=$left item=cell}
      {include file="dialog_tools_cell.tpl" cell=$cell}
{/foreach}
      </ul>
{/if}

{if $right}
{if $right.data}
{assign var=right value=$right.data}
{/if}
      <ul class="dialog-tools-content dialog-tools-right{if $left and $dialog_tools_data.show ne "right"} hidden{/if}">
{foreach from=$right item=cell}
      {include file="dialog_tools_cell.tpl" cell=$cell}
{/foreach}
      </ul>
{/if}

    </div>

  </div>

  </td>
</tr>
</table>
{/if}
