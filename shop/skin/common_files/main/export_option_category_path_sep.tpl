{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, export_option_category_path_sep.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
<table cellpadding="1" cellspacing="1" width="100%">
<tr>
  <td><b>{$lng.txt_category_path_sep}:</b></td>
</tr>
<tr>
  <td><input type="text" name="options[category_sep]" value="{$export_data.category_sep|default:"/"|escape}" /></td>
</tr>
<tr>
  <td>{$lng.txt_category_path_sep_explain}</td>
</tr>
</table>
