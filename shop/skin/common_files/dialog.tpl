{*
acbc09d75c62bda4f9faa1255d69230ae8a38c23, v2 (xcart_4_5_3), 2012-08-29 10:38:47, dialog.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{if $title}
  <h2>{$title}</h2>
{/if}
<table cellspacing="0" {$extra}>
<tr>
  <td class="DialogBorder">
    <table cellspacing="{if not $zero_cellspacing}1{else}0{/if}" class="DialogBox">
      <tr>
        <td class="DialogBox" valign="{$valign|default:"top"}">
          {$content}&nbsp;
        </td>
      </tr>
    </table>
  </td>
</tr>
</table>
