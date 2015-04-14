{*
5eba2fb2ff75df4a18d07913b095c0b884b1e772, v1 (xcart_4_6_4), 2014-06-25 11:08:30, admin_tab_mapping_rules.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
<form action="configuration.php?option=XPayments_Connector" method="POST" name="xpc_mapping_rules">
{include file="modules/XPayments_Connector/admin_options_list.tpl" options_list=$xpc_configuration.mapping_rules}
{include file="modules/XPayments_Connector/admin_hidden_options.tpl" skip='mapping_rules'}
<br /><br />
<div class="main-button">
  <input type="submit" value="{$lng.lbl_apply_changes}" class="big-main-button" />
</div>
</form>
