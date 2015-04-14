{*
71ae8da1574e2d17ed36dafb837750799af54781, v1 (xcart_4_6_0), 2013-04-05 15:51:07, opc_payment_methods_conditions.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{if $payment.processor eq "cc_klarna.php"}
  <div id="invoice_pm_conditions">
    {include file="modules/Klarna_Payments/condition_js.tpl" elementid='invoice_pm_conditions' is_invoice_payment="Y"}
  </div>
{/if}
{if $payment.processor eq "cc_klarna_pp.php"}
  <div id="pp_pm_conditions">
    {include file="modules/Klarna_Payments/condition_js.tpl" elementid='pp_pm_conditions'}
  </div>
{/if}

