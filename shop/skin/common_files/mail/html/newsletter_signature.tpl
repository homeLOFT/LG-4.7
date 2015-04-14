{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, newsletter_signature.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
<hr size="1" noshade="noshade" />
{$lng.eml_unsubscribe_information}
<br />
<a href="{$http_location}/mail/unsubscribe.php?email={$email|escape}&listid={$listid}">{$http_location}/mail/unsubscribe.php?email={$email|escape}&amp;listid={$listid}</a>

{include file="mail/html/signature.tpl"}

