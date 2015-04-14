{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, import_option_default_category.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
<table cellpadding="1" cellspacing="1" width="100%">
<tr>
  <td><b>{$lng.txt_default_category}:</b></td>
</tr>
<tr>
  <td>{include file="main/category_selector.tpl" field="options[categoryid]" categoryid=$import_data.options.categoryid|default:0}</td>
</tr>
<tr>
  <td>{$lng.txt_default_category_explain}</td>
</tr>
</table>
