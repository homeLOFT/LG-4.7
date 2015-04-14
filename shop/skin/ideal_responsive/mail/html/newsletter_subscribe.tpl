{*
e94f191a2cc0cb8dbefd384fabaa6f48af5f3c11, v1 (xcart_4_6_2), 2013-12-25 09:14:58, newsletter_subscribe.tpl, random
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
