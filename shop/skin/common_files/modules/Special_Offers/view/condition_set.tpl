{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, condition_set.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
<table width="100%" cellspacing="0" cellpadding="0" border="0">
<tr>
  <td>{include file="modules/Special_Offers/view/product_n_category.tpl" item=$condition item_type="C" with_qnty="Y" join_type="and"}</td>
</tr>
<tr>
  <td>&nbsp;</td>
</tr>
<tr>
  <td>{$lng.lbl_sp_condition_set_action}: <br />{if $condition.amount_type eq "N"}<i>{$lng.lbl_sp_action_copy}</i>{else}<i>{$lng.lbl_sp_action_one}</i>{/if}</td>
</tr>
</table>
