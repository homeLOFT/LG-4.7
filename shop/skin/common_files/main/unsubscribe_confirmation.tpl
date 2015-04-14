{*
1a575cb1ffad3d49849edc0cc4c536c7ccb6c0c5, v2 (xcart_4_6_1), 2013-08-28 14:15:32, unsubscribe_confirmation.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{capture name=dialog}
{$lng.txt_unsubscribed_msg}<br />
{$lng.lbl_email}: <b>{$smarty.get.email|escape:"html"|replace:"\\":""}</b>
{/capture}
{include file="dialog.tpl" title=$lng.txt_thankyou_for_unsubscription content=$smarty.capture.dialog extra='width="100%"'}
