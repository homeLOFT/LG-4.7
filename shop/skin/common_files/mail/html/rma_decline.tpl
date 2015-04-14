{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, rma_decline.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{config_load file="$skin_config"}
{include file="mail/html/mail_header.tpl"}

<br />{include file="mail/salutation.tpl" title=$userinfo.title firstname=$userinfo.firstname lastname=$userinfo.lastname}<br />
<br />
{$lng.eml_rma_return_declined|substitute:"returnid":$return.returnid}<br />
<br />
{include file="modules/RMA/return_data.tpl"}
<br />
{include file="mail/html/signature.tpl"}
