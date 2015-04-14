{*
f7f946996b3f74495f07af7fc9812865f7e93875, v2 (xcart_4_6_5), 2014-08-04 14:12:36, admin_tab_connection.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
<form action="configuration.php?option=XPayments_Connector" method="POST" name="xpc_config">
{include file="modules/XPayments_Connector/admin_options_list.tpl" options_list=$xpc_configuration.common}
{include file="modules/XPayments_Connector/admin_hidden_options.tpl" skip='common'}
<div class="xpc-advanced-options-link"><a href="xpc_admin.php?mode=deploy_configuration">{$lng.lbl_xpc_deploy_new_bundle}</a></div>
<br /><br />
<div class="main-button">
  <input type="submit" value="{$lng.lbl_apply_changes}" class="big-main-button" />
</div>
</form>
