{*
c0b7a742f56d4cd9d5875fb9a121be48c4674208, v4 (xcart_4_7_0), 2014-12-20 17:15:41, edit_giftcerts.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{capture name=dialog}
<form action="order.php" method="post" name="editgc_form">
<input type="hidden" name="mode" value="edit" />
<input type="hidden" name="action" value="update_giftcerts" />
<input type="hidden" name="show" value="giftcerts" />
<input type="hidden" name="orderid" value="{$orderid}" />

{include file="main/subheader.tpl" title=$lng.lbl_gift_certificates}

<table cellspacing="1" cellpadding="3" width="100%">

{if $cart_giftcerts}
{section name=gc loop=$cart_giftcerts}
{assign var="giftcert" value=$cart_giftcerts[gc]}
{assign var="orig_giftcert" value=$orig_giftcerts[gc]}
<tr>
  <td colspan="3" height="18" class="{if $giftcert.deleted}ProductTitleHidden{else}ProductTitle{/if}">
  <a href="giftcerts.php?mode=modify_gc&amp;gcid={$giftcert.gcid}" target="viewgc{$giftcert.gcid}">{$lng.lbl_gc_id} #{$giftcert.gcid}</a>
{if $giftcert.deleted}
  &nbsp;&nbsp;&nbsp;[<font class="ErrorMessage">{$lng.lbl_aom_deleted}</font>]
{/if}
  </td>
</tr>
<tr class="TableHead">
  <td height="16" align="left">

<table cellpadding="0" cellspacing="0">
<tr>
  <td><input type="checkbox" name="giftcert_details[{$smarty.section.gc.index}][delete]" value="{$giftcert.gcid|escape}" /></td>
  <td>{if $giftcert.deleted}{$lng.lbl_aom_restore}{else}{$lng.lbl_aom_delete}{/if}</td>
</tr>
</table>

  </td>
  <th height="16" align="left">{$lng.lbl_aom_current_value}</th>
  <th height="16" align="left">{$lng.lbl_aom_original_value}</th>
</tr>
<tr>
  <td valign="top">{$lng.lbl_amount}</td>
  <td valign="top">{if $giftcert.deleted}{currency value=$giftcert.amount}{else}<input type="text" name="giftcert_details[{$smarty.section.gc.index}][amount]" size="15" maxlength="15" value="{$giftcert.amount|formatprice}" />{/if}</td>
  <td valign="top">{currency value=$orig_giftcert.amount}</td>
</tr>
<tr class="TableSubHead">
  <td valign="top">{$lng.lbl_recipient}</td>
  <td valign="top">{$giftcert.recipient}</td>
  <td valign="top">{$orig_giftcert.recipient}</td>
</tr>
{if $giftcert.send_via eq "P"}
<tr>
  <td colspan="3">{$lng.lbl_gc_send_via_postal_mail}</td>
</tr>
<tr>
  <td valign="top" class="LabelStyle">{$lng.lbl_mail_address}</td>
  <td valign="top">
{$giftcert.recipient_firstname} {$giftcert.recipient_lastname}<br />
{$giftcert.recipient_address}, {$giftcert.recipient_city},<br />
{$giftcert.recipient_state} {$giftcert.recipient_country}, {$giftcert.recipient_zipcode}
</td>
  <td valign="top">
{$orig_giftcert.recipient_firstname} {$orig_giftcert.recipient_lastname}<br />
{$orig_giftcert.recipient_address}, {$orig_giftcert.recipient_city},<br />
{$orig_giftcert.recipient_state} {$orig_giftcert.recipient_country}, {$orig_giftcert.recipient_zipcode}
</td>
</tr>
<tr>
  <td valign="top" class="LabelStyle">{$lng.lbl_phone}</td>
  <td valign="top">{$giftcert.recipient_phone}</td>
  <td valign="top">{$orig_giftcert.recipient_phone}</td>
</tr>
{else}
<tr>
  <td valign="top">{$lng.lbl_recipient_email}</td>
  <td valign="top">{$giftcert.recipient_email}</td>
  <td valign="top">{$orig_giftcert.recipient_email}</td>
</tr>
{/if}
<tr>
  <td valign="top" colspan="3" height="10">&nbsp;</td>
</tr>
{/section}
<tr>
  <td colspan="3"><br />
<input type="submit" value="{$lng.lbl_update}" />
<br /><br />
  </td>
</tr>
{else}
<tr>
  <th colspan="3">{$lng.lbl_aom_no_giftcerts}</th>
</tr>
{/if}
</table>
</form>
{/capture}
{include file="dialog.tpl" title=$lng.lbl_aom_edit_giftcerts_title|substitute:"orderid":$order.orderid content=$smarty.capture.dialog extra='width="100%"'}
