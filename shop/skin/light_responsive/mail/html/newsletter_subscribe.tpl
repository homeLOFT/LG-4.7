{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, newsletter_subscribe.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{config_load file="$skin_config"}
{include file="mail/html/mail_header.tpl"}

{capture name="row"}
{$lng.eml_subscribed}

<br />{$lng.eml_unsubscribe_information}
<br />
<a href="{$http_location}/mail/unsubscribe.php?email={$email|escape}">{$http_location}/mail/unsubscribe.php?email={$email|escape}</a>
{/capture}
{include file="mail/html/responsive_row.tpl" content=$smarty.capture.row}

{include file="mail/html/signature.tpl"}
