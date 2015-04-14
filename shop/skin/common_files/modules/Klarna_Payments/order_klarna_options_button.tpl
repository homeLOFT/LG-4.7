{*
71ae8da1574e2d17ed36dafb837750799af54781, v1 (xcart_4_6_0), 2013-04-05 15:51:07, order_klarna_options_button.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{if $is_klarna_payment == 'Y'}
  {if $order.status eq 'A' and $order.klarna_order_status eq 'P'}
	  <td class="ButtonsRow">{include file="buttons/button.tpl" button_title=$lng.lbl_klarna_check_status href="javascript: self.location = 'process_order.php?orderid=`$order.orderid`&mode=klarna_check_order_status';" substyle="return"}</td>
  {/if}
{/if}
