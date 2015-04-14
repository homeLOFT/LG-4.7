{*
1a575cb1ffad3d49849edc0cc4c536c7ccb6c0c5, v3 (xcart_4_6_1), 2013-08-28 14:15:32, subscribe_confirmation.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{capture name=dialog}
{$lng.txt_newsletter_subscription_msg}:<br />
<b>{$smarty.get.email|escape:"html"|replace:"\\":""}</b>
<br />
{$lng.txt_unsubscribe_information} <a href="{$http_location}/mail/unsubscribe.php?email={$smarty.get.email|escape|replace:"\\":""}"><font class="FormButton">{$lng.lbl_this_url}</font></a>.
{/capture}
{include file="dialog.tpl" title=$lng.txt_thankyou_for_subscription content=$smarty.capture.dialog extra='width="100%"'}
