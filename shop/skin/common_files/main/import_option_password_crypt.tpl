{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, import_option_password_crypt.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
<table cellpadding="1" cellspacing="1" width="100%">
<tr>
  <td><b>{$lng.lbl_imported_customer_passwords_encrypted}:</b></td>
</tr>
<tr>
  <td><select name="options[crypt_password]">
  <option value="Y"{if $import_data.options.crypt_password eq 'Y' or $import_data eq ''} selected="selected"{/if}>{$lng.lbl_yes}</option>
  <option value=""{if $import_data.options.crypt_password eq '' and $import_data ne ''} selected="selected"{/if}>{$lng.lbl_no}</option>
  </select ></td>
</tr>
</table>
