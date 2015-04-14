{*
71ae8da1574e2d17ed36dafb837750799af54781, v1 (xcart_4_6_0), 2013-04-05 15:51:07, condition_js.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
<script type="text/javascript">
//<![CDATA[
{if $is_invoice_payment eq 'Y'}
  var terms = new Klarna.Terms.Invoice({ldelim}
{else}
  var terms = new Klarna.Terms.Account({ldelim}
{/if}
    el: '{$elementid}{if $is_new_arrivals_products eq "Y"}_new{/if}',                  // The element id of the element you want to use.  
                                         // Alternatively you could use an element directly, 
                                         // for example document.getElementsById('#my-link') or jQuery('span.invoice', '#terms').get(0);  
    eid: {$config.Klarna_Payments.klarna_default_eid},                // Your merchant ID  
    country: '{$config.Klarna_Payments.user_country}',      // country code (ISO 3166-1 alpha-2   
                                                              // or ISO 3166-1 alpha-3 code)  
    charge: {if $is_invoice_payment eq 'Y'}{$config.Klarna_Payments.invoice_payment_surcharge}{else}0{/if}                    // the invoice fee charged, defaulted to 0  
  {rdelim})  
//]]>
</script> 
