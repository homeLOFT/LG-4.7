{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, rma_decline.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{config_load file="$skin_config"}
{include file="mail/html/mail_header.tpl"}

{capture name="row"}
<h1>{include file="mail/salutation.tpl" title=$userinfo.title firstname=$userinfo.firstname lastname=$userinfo.lastname}</h1>

{$lng.eml_rma_return_declined|substitute:"returnid":$return.returnid}
<br /><br />
{include file="modules/RMA/return_data.tpl"}
{/capture}
{include file="mail/html/responsive_row.tpl" content=$smarty.capture.row}

{include file="mail/html/signature.tpl"}
