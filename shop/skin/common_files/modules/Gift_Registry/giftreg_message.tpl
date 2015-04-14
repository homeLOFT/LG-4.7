{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, giftreg_message.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{if $message ne ""}

{capture name="dialog"}

{if $message eq "confirmed"}
{$lng.txt_confirmation_msg}

{elseif $message eq "declined"}
{$lng.txt_decline_msg}

{elseif $message eq "maillist_imported"}
{$lng.txt_maillist_import_msg}

{elseif $message eq "notification_sent"}

{$lng.txt_notification_sent_msg}<br />
<ul>
{foreach from=$recipients_sent item=r}
  <li>{$r.recipient_name} &lt;{$r.recipient_email}&gt;</li>
{/foreach}
</ul>

{/if}
{/capture}
{include file="customer/dialog.tpl" title=$lng.lbl_message content=$smarty.capture.dialog}

{/if}

