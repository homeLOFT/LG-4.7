{*
71ae8da1574e2d17ed36dafb837750799af54781, v1 (xcart_4_6_0), 2013-04-05 15:51:07, opc_payment_methods_logo.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
<br />
{if $payment.processor eq "cc_klarna.php"}
  <img src="https://cdn.klarna.com/public/images/{$config.Klarna_Payments.user_country|upper}/badges/v1/invoice/{$config.Klarna_Payments.user_country|upper}_invoice_badge_std_blue.png?width=110&eid={$config.Klarna_Payments.klarna_default_eid}" alt="Klarna factura" /><br />
{elseif $payment.processor eq "cc_klarna_pp.php"}
  <img src="https://cdn.klarna.com/public/images/{$config.Klarna_Payments.user_country|upper}/badges/v1/account/{$config.Klarna_Payments.user_country|upper}_account_badge_std_blue.png?width=110&eid={$config.Klarna_Payments.klarna_default_eid}" alt="Klarna factura" /><br />
{/if}

