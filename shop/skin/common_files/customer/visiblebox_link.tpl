{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, visiblebox_link.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
<div class="expand-section">
  {strip}
    <img src="{$ImagesDir}/spacer.gif" class="plus" id="{$id}_plus" alt="{$lng.lbl_click_to_open|escape}"{if $visible} style="display: none;"{/if} onclick="javascript: switchVisibleBox('{$id}');" />
    <img src="{$ImagesDir}/spacer.gif" class="minus" id="{$id}_minus"{if not $visible} style="display: none;"{/if} alt="{$lng.lbl_click_to_close|escape}" onclick="javascript: switchVisibleBox('{$id}');" />
    <a href="javascript:void(0);" onclick="javascript: switchVisibleBox('{$id}');">{$title}</a>
  {/strip}
</div>
