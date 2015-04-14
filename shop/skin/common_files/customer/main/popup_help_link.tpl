{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, popup_help_link.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{if $section}
  <a href="popup_info.php?action={$section}" onclick="javascript: return typeof(window.popupOpen) == 'undefined' || !popupOpen('popup_info.php?action={$section}', '{$title|wm_remove|escape:javascript}');" class="popup-link" target="_blank"><img src="{$ImagesDir}/spacer.gif" alt="{$lng.lbl_popup_help|escape}" /></a>
{/if}
