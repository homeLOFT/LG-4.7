{*
9e7365e696d7eec04d02f9c9d1bda4acaf1bfb61, v2 (xcart_4_6_3), 2014-02-28 17:47:25, order_message_widget.tpl, mixon
vim: set ts=2 sw=2 sts=2 et:
*}

<h1>{$lng.lbl_invoice}</h1>

{if $this_is_printable_version eq ""}

  {capture name=dialog}

    <p class="text-block">{$lng.txt_order_placed}</p>
    {$lng.txt_order_placed_msg}

  {/capture}
  {include file="customer/dialog.tpl" title=$lng.lbl_confirmation content=$smarty.capture.dialog}

{/if}

<div id="amazonOrderDetail" class="halign-center"></div>
<script>
$(document).ready(function(){ldelim}
  if (typeof(CBA) === 'object' && typeof(CBA.Widgets.StandardCheckoutWidget) === 'function')
  {ldelim}
    new CBA.Widgets.OrderDetailsWidget ({ldelim}
      merchantId: '{$config.Amazon_Checkout.amazon_mid}',
      orderID: '{$amazon_orderid}'
      {rdelim}).render ("amazonOrderDetail");
  {rdelim}
{rdelim});
</script>
