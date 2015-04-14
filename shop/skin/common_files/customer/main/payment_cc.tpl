{*
b3621e54de65ee45fb84e4e087fedd08672fd118, v5 (xcart_4_4_6), 2012-02-14 12:49:45, payment_cc.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{if $payment_cc_data.background eq 'I'}
  {$lng.disable_ccinfo_iframe_msg}
{else}
  {$lng.disable_ccinfo_msg}
{/if}
<br />
