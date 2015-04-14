{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, Password_Recovery_message.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}

<h1>{$lng.lbl_confirmation}</h1>

{capture name=dialog}
  {$lng.txt_password_recover_message1} {$smarty.get.email|escape:"html"}.
  {$lng.txt_password_recover_message2}
{/capture}
{include file="customer/dialog.tpl" title=$lng.lbl_confirmation content=$smarty.capture.dialog noborder=true}
