{*
b7523eaa53be3fa0e9cbcae68f5534198746ebc0, v2 (xcart_4_4_6), 2012-02-02 08:10:51, register_ccmulticard.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
<table cellspacing="0" cellpadding="2">

<tr valign="middle">
  <td align="right">{$lng.lbl_credit_card_type}:</td>
  <td><font class="Star">*</font></td>
  <td nowrap="nowrap" colspan="3">

<select name="pay_method_type">
  <option value="creditcard_visa">Visa</option>
  <option value="creditcard_mastercard">Mastercard</option>
  <option value="creditcard_eurocard">Eurocard</option>
  <option value="creditcard_americanexpress">American Express</option>
  <option value="creditcard_discover">Discover</option>
  <option value="creditcard_dinersclub">Diners Club</option>
  <option value="creditcard_carteblanche">Carte Blanche</option>
  <option value="creditcard_jcb">JCB</option>
  <option value="bank_directdebit">Bank transafer (USA only)</option>
</select>

  </td>
</tr>

</table>
