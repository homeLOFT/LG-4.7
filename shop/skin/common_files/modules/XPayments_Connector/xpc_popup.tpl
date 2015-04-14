{*
686a4c944b1bab093a0d60e12aff76ce8db05664, v3 (xcart_4_7_0), 2015-02-18 13:16:20, xpc_popup.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
<!-- MAIN -->
{if $message ne ''}
  {assign var="message" value=$message|escape}
  {if $type ne $smarty.const.XPC_IFRAME_ALERT}
    {$lng.txt_xpc_checkout_error|substitute:'message':$message}
  {else}
    {$message}
  {/if}
{else}
  {$lng.txt_ajax_error_note}
{/if}
<!-- /MAIN -->
