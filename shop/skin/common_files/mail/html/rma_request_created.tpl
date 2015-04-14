{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, rma_request_created.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{include file="mail/html/mail_header.tpl"}
<br />{$lng.eml_rma_request_created|substitute:"creator":"`$userinfo.firstname` `$userinfo.lastname`"}<br />
<br />
{$lng.eml_return_requests}:<br />
{foreach from=$returns item=v}
<hr />
{include file="modules/RMA/return_data.tpl" return=$v}
{/foreach}
</table>
<br />
{include file="mail/html/signature.tpl"}
