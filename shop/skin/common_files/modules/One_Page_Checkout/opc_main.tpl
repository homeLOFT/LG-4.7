{*
1b517203fe38a899ebc8fdc5ad481983b7e0a607, v14 (xcart_4_6_2), 2014-02-02 09:49:59, opc_main.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{include file="modules/One_Page_Checkout/opc_init_js.tpl"}
{load_defer file="modules/One_Page_Checkout/ajax.checkout.js" type="js"}
{if $active_modules.TaxCloud}
  {include file="modules/TaxCloud/exemption_js.tpl"}
{/if}
{if $active_modules.Abandoned_Cart_Reminder && $login eq '' && $config.Abandoned_Cart_Reminder.abcr_ajax_save eq 'Y'}
  {load_defer file="modules/Abandoned_Cart_Reminder/checkout.js" type="js"}
{/if}

<h1>{$lng.lbl_checkout}</h1>

{include file="modules/One_Page_Checkout/opc_authbox.tpl"}

<ul id="opc-sections">
  <li class="opc-section">
    {include file="modules/One_Page_Checkout/opc_profile.tpl"}
  </li>

  <li class="opc-section" id="opc_shipping_payment">

    {if $config.Shipping.enable_shipping eq "Y"}
      {include file="modules/One_Page_Checkout/opc_shipping.tpl"}
    {/if}

    {include file="modules/One_Page_Checkout/opc_payment.tpl"}

  </li>

  <li class="opc-section last" id="opc_summary_li">
    {include file="modules/One_Page_Checkout/opc_summary.tpl"}
  </li>

</ul>

{include file="customer/noscript.tpl" content=$lng.txt_opc_noscript_warning}
