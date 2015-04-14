{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, error_cmpi_error.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}

<h1>{$lng.lbl_order_processing_error}</h1>

<div class="error-message">{$lng.err_cmpi_declined_order}</div>

{if $smarty.get.bill_message ne ""}
<div class="text-block">
  <span class="form-text">{$lng.err_payment_reason}:</span>
  {$smarty.get.bill_message|escape|nl2br}
</div>
{/if}

{include file="customer/buttons/go_back.tpl" href="`$catalogs.customer`/cart.php?mode=checkout"}
