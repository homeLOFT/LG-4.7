{*
9b5352f179bbebcbab7da1c7ec1c274c63d2b506, v1 (xcart_4_6_5), 2014-10-06 05:53:45, payment_xpc.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{if $active_modules.XPayments_Connector}
  {if $config.XPayments_Connector.xpc_use_iframe eq 'Y'}
    {include file="modules/XPayments_Connector/xpc_iframe.tpl"}
  {else}
    {include file="modules/XPayments_Connector/xpc_separate.tpl"}
  {/if}
{else}
  {$lng.err_payment_cc_not_available}
{/if}
