{*
9801da6f17425f50e8047475e08712045e9818fd, v2 (xcart_4_6_1), 2013-08-29 12:26:36, authentication.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{if $is_https_zone}
  <h1>{$lng.lbl_secure_login_form}</h1>
  <p class="text-block">{$lng.txt_secure_login_form}</p>
{else}
  <h1>{$lng.lbl_authentication}</h1>
{/if}

{capture name=dialog}

  {include file="customer/main/login_form.tpl"}

  {if not $is_flc}
    <br />
    {$lng.txt_new_account_msg}
  {/if}

{/capture}
{include file="customer/dialog.tpl" title=$lng.lbl_authentication content=$smarty.capture.dialog noborder=true}
