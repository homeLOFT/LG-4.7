{*
9b60d75728318a07bf48c3e1baed27812035ad61, v3 (xcart_4_7_0), 2015-03-02 11:01:49, confirm_deletion.tpl, mixon

vim: set ts=2 sw=2 sts=2 et:
*}
<form action="order.php" method="post" name="confirmation_form">

<input type="hidden" name="mode" value="edit" />
<input type="hidden" name="action" value="delete" />
<input type="hidden" name="orderid" value="{$orderid}" />
<input type="hidden" name="confirmed" value="Y" />

<table cellpadding="0" cellspacing="0" width="100%">
<tr>
  <td colspan="2">
  {$lng.txt_aom_confirm_deletion_order}
  <br /><br />
  </td>
</tr>
<tr>
  <td colspan="2">
<table cellspacing="0" cellpadding="0">
<tr>
  <td class="ButtonsRow">{include file="buttons/yes.tpl" href="javascript:document.confirmation_form.submit();" js_to_href="Y"}</td>
  <td class="ButtonsRow">{include file="buttons/no.tpl" href="order.php?orderid=`$orderid`&mode=edit&show=preview"}</td>
</tr>
</table>
  </td>
</tr>
</table>

</form>
