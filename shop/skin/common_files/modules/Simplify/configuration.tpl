{*
aad85f2be3e7abf71f38a7de369cbd6188fe4f3b, v2 (xcart_4_7_0), 2015-02-11 09:38:53, configuration.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}

<form action="configuration.php?option={$option|escape}" method="post" name="processform" onsubmit="return validateFields()">

{include file="admin/main/conf_fields_validation_js.tpl"}

{include file="customer/main/ui_tabs.tpl" prefix="simplify-tabs-" mode="inline" default_tab="-1last_used_tab" tabs=$integration_type_tabs}

</form>
