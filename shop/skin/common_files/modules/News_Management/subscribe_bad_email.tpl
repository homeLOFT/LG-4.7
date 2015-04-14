{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, subscribe_bad_email.tpl, joy 
vim: set ts=2 sw=2 sts=2 et:
*}

<h1>{$lng.txt_unsubscribe_email}</h1>

{capture name=dialog}

  <p class="text-block">
    {$lng.txt_unsubscribe_bad_email}
  </p>

{/capture}
{include file="customer/dialog.tpl" title=$lng.txt_unsubscribe_email content=$smarty.capture.dialog noborder=true}
