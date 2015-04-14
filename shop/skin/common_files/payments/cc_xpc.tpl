{*
47fc90b0d1c9e69e0dc7372473be683238da67a2, v7 (xcart_4_6_4), 2014-06-26 13:22:17, cc_xpc.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
<h1>{$lng.lbl_xpc_xpayments_methods}</h1>

<br />
<br />

{capture name=dialog}

<img src="{$ImagesDir}/xpc_logo.png" alt="X-Payments logo" />

<br />
<br />

{$lng.txt_xpc_pm_config_note_2|substitute:'url':$config.XPayments_Connector.xpc_xpayments_url}

<br />
<br />

<a href="xpc_admin#xpc-tabs-payment-methods">{$lng.lbl_xpc_xpayments_connector_settings}</a>

<br />
<br />

{/capture}
{include file="dialog.tpl" title=$lng.lbl_cc_settings content=$smarty.capture.dialog extra='width="100%"'}
