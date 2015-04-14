{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, mail_header.tpl, aim

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
