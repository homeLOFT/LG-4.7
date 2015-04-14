{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, offer_conditions.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
<table cellpadding="3" cellspacing="1" width="100%">

<tr>
  <td>
<form action="offers.php" method="post" name="wizardform">
<input type="hidden" name="mode" value="conditions" />
<input type="hidden" name="action" value="delete" />
<input type="hidden" name="offerid" value="{$offerid}" />

{include file="modules/Special_Offers/wizard_step_w_list.tpl" items=$conditions}

</form>
  </td>
</tr>
</table>
