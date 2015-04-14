{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, newsletter_signature.tpl, aim

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
