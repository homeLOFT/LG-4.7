{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, visiblebox_link.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
<table cellpadding="1" cellspacing="5" width="100%">
  <tr>
    <td>
      <table cellpadding="2" cellspacing="2">
        <tr>
          <td {if $no_use_class eq "Y"}{else}class="ExpandSectionMark"{/if} id="close{$mark}" onclick="javascript: visibleBox('{$mark}');"{if $visible} style="display: none;"{/if}><img src="{$ImagesDir}/plus.gif" alt="{$lng.lbl_click_to_open|escape}" /></td>
          <td {if $no_use_class eq "Y"}{else}class="ExpandSectionMark"{/if} id="open{$mark}" onclick="javascript: visibleBox('{$mark}');"{if not $visible} style="display: none;"{/if}><img src="{$ImagesDir}/minus.gif" alt="{$lng.lbl_click_to_close|escape}" /></td>
          <td nowrap="nowrap" class="ExpandSectionText"><a href="javascript:void(0);" onclick="javascript: visibleBox('{$mark}');"><b>{$title}</b></a></td>
        </tr>
      </table>
    </td>
  </tr>
</table>
