{*
e94f191a2cc0cb8dbefd384fabaa6f48af5f3c11, v1 (xcart_4_6_2), 2013-12-25 09:14:58, newsletter_signature.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{capture name="row"}
<hr size="1" noshade="noshade" />
{$lng.eml_unsubscribe_information}
<br />
<a href="{$http_location}/mail/unsubscribe.php?email={$email|escape}&listid={$listid}">{$http_location}/mail/unsubscribe.php?email={$email|escape}&amp;listid={$listid}</a>
{/capture}
{include file="mail/html/responsive_row.tpl" content=$smarty.capture.row}

{include file="mail/html/signature.tpl"}
