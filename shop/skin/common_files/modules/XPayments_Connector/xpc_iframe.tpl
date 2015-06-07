{*
-5eba2fb2ff75df4a18d07913b095c0b884b1e772, v7 (xcart_4_6_4), 2014-06-25 11:08:30, xpc_iframe.tpl, random
+b02cf461c759a002346dc46a8e3b3925dca89fe1, v9 (xcart_4_7_1), 2015-03-25 15:52:11, xpc_iframe.tpl, aim
+
 vim: set ts=2 sw=2 sts=2 et:
 *}
-
 {* Draws iframe container *}
 {if $save_cc}
   <iframe width="280" height="100" border="0" marginheight="0" marginwidth="0" frameborder="0" class="xpc_iframe" id="xpc_iframe" name="xpc_iframe">
   </iframe>
 {else}
+<div class="xpc-iframe-container">
 {if $active_modules.One_Page_Checkout}
 
   <script type="text/javascript">
    xpc_paymentids[{$payment.paymentid}] = {$payment.paymentid};
  </script>

  <iframe width="100%" height="335" border="0" marginheight="0" marginwidth="0" frameborder="0" class="xpc_iframe" id="xpc_iframe{$payment.paymentid}" name="xpc_iframe{$payment.paymentid}">
  </iframe>

{elseif $active_modules.Fast_Lane_Checkout}

  <a name="payment_details"></a>

  <div class="xpc_iframe_container">
    <iframe width="100%" height="100" border="0" marginheight="0" marginwidth="0" frameborder="0" class="xpc_iframe" id="xpc_iframe" name="xpc_iframe" src="payment/cc_xpc_iframe.php?paymentid={$paymentid}">
    </iframe>
  </div>

  <script type="text/javascript">
    xpc_iframe_method = true;

    if (window.location.hash == '')
      window.location.hash = 'payment_details';

    $('.xpc_iframe_container').block();
  </script>

{/if}

{include file="modules/XPayments_Connector/allow_recharges.tpl"}
</div>
{/if}
