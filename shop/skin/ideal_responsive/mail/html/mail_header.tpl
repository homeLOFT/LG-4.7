{*
e94f191a2cc0cb8dbefd384fabaa6f48af5f3c11, v1 (xcart_4_6_2), 2013-12-25 09:14:58, mail_header.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{include file="mail/html/responsive_row.tpl" content="<img src='`$AltImagesDir`/custom/logo.png' alt='' />"}
{capture name="row"}
<font class="text-muted" size="1">
{assign var="link" value="<a href=\"$http_location/\" target=\"_blank\">`$config.Company.company_name`</a>"}
{$lng.eml_mail_header|substitute:"company":$link}
</font>
{/capture}
{include file="mail/html/responsive_row.tpl" content=$smarty.capture.row} 
