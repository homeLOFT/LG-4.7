{*
71ae8da1574e2d17ed36dafb837750799af54781, v1 (xcart_4_6_0), 2013-04-05 15:51:07, klarna_invoice_info.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{if $order.extra.reservation_id neq '' and $order.extra.klarna_invoice_id eq ''}
{$lng.lbl_klarna_reservation_id}:{$order.extra.reservation_id}
{else $order.extra.klarna_invoice_id neq ''}
{$lng.lbl_klarna_invoice_id}:{$order.extra.klarna_invoice_id}}
{/if}
