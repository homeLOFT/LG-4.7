{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, orders_tracking.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{*850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, orders_tracking.tpl, joy*}
{if $usertype eq "A" or $usertype eq "P"}
{capture name=dialog}
<a name="OrderTracking"></a>
<div align="right">
{include file="buttons/button.tpl" button_title=$lng.lbl_import_trackingid_help href="javascript:window.open('popup_info.php?action=IMP','IMP_HELP','width=600,height=460,toolbar=no,status=no,scrollbars=yes,resizable=no,menubar=no,location=no,direction=no');"}
</div>

<br />

{$lng.txt_import_trackingid}

<br />
<br />

<form name="importform" action="process_order.php" method="post" enctype="multipart/form-data">
<input type="hidden" name="mode" value="tracking_data" />

<table cellpadding="0" cellspacing="3">
<tr>
  <td width="15%"><b>{$lng.lbl_import_csv}:</b>&nbsp;&nbsp;</td>
  <td width="85%"><input type="file" name="userfile" /></td>
</tr>

<tr>
  <td colspan="2"><br /><input type="submit" value="{$lng.lbl_import|escape}" /></td>
</tr>

</table>

</form>

{/capture}
{include file="dialog.tpl" title=$lng.lbl_import_trackingid_file content=$smarty.capture.dialog extra='width="100%"'}
{/if}
