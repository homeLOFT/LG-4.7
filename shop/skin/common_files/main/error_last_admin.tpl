{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, error_last_admin.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{capture name=dialog}

{$lng.txt_last_admin_warning}

<br /><br />

{include file="buttons/button.tpl" button_title=$lng.lbl_continue href="home.php"}

<br />

{/capture}
{include file="dialog.tpl" title=$lng.lbl_warning content=$smarty.capture.dialog extra='width="100%"'}
