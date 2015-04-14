{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, check_all_row.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
<script type="text/javascript" src="{$SkinDir}/js/change_all_checkboxes.js"></script>
<div{if $style ne ''} style="{$style}"{/if}><a href="javascript:checkAll(true,document.{$form},'{$prefix}');">{$lng.lbl_check_all}</a> / <a href="javascript:checkAll(false,document.{$form},'{$prefix}');">{$lng.lbl_uncheck_all}</a></div>
