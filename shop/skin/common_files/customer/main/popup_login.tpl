{*
20c685f3573b2ddb53479e4db7ab77f8b74001ad, v2 (xcart_4_6_1), 2013-08-29 12:19:36, popup_login.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
<h1>{$lng.lbl_sign_in}</h1>

<p id="login-error" class="error-message" style="display:none;"></p>

{capture name=dialog}

  {include file="customer/main/login_form.tpl"}

{/capture}
{include file="customer/dialog.tpl" title=$lng.lbl_authentication content=$smarty.capture.dialog noborder=true}
