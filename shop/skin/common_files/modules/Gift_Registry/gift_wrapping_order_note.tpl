{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, gift_wrapping_order_note.tpl, joy 
vim: set ts=2 sw=2 sts=2 et:
*}
<tr>
  <td colspan="2"><strong>{$lng.lbl_giftreg_wrap_order_note}</strong></td>
</tr>
{if $order.giftwrap_message ne ""}
<tr>
  <td colspan="2">{$lng.lbl_giftreg_greeting_message}:
<div class="greeting-message">{$order.giftwrap_message|nl2br}</div>
  </td>
</tr>
{else}
<tr>
  <td colspan="2" height="10">&nbsp;</td>
</tr>
{/if}
