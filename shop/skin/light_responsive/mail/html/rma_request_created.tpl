{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, rma_request_created.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{include file="mail/html/mail_header.tpl"}

{capture name="row"}
{$lng.eml_rma_request_created|substitute:"creator":"`$userinfo.firstname` `$userinfo.lastname`"}
<br /><br />
{$lng.eml_return_requests}:<br />
{foreach from=$returns item=v}
<hr />
{include file="modules/RMA/return_data.tpl" return=$v}
{/foreach}
{/capture}
{include file="mail/html/responsive_row.tpl" content=$smarty.capture.row}

{include file="mail/html/signature.tpl"}
