{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, product_notification.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{config_load file="$skin_config"}
{include file="mail/html/mail_header.tpl"}

{capture name="row"}
<h1>{include file="mail/salutation.tpl" title=$userinfo.title firstname=$userinfo.firstname lastname=$userinfo.lastname}</h1>

{assign var=notification_tpl_name value="mail/html/product_notification_`$type`.tpl"}
{include file=$notification_tpl_name}

{assign var=config_unsubscribe_flag_name value="prod_notif_send_unsub_link_`$type`"}
{if $config.Product_Notifications.$config_unsubscribe_flag_name eq 'Y'}
{$lng.eml_prod_notif_unsubscribe|substitute:"url":$notification_data.unsubscribe_url}
{/if}
{/capture}
{include file="mail/html/responsive_row.tpl" content=$smarty.capture.row}

{include file="mail/html/signature.tpl"}
