{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, dialog_tools_cell.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{if $cell.separator}
<li class="dialog-cell-separator"><img src="{$ImagesDir}/spacer.gif" alt="" /></li>
{else}
<li>
  <a class="dialog-cell{if $cell.style eq "hl"}-hl{/if}" href="{$cell.link|amp}" title="{$cell.title|escape}"{if $cell.target ne ""} target="{$cell.target}"{/if}>
    {$cell.title}
  </a>
</li>
{/if}
