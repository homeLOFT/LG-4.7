{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, export_option_export_images.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
<table cellpadding="1" cellspacing="1" width="100%">
<tr>
  <td><b>{$lng.lbl_do_you_wish_to_export_images}</b></td>
</tr>
<tr>
  <td><select name="options[export_images]">
  <option value="Y"{if $export_data.export_images eq 'Y' or $export_data eq ''} selected="selected"{/if}>{$lng.lbl_yes}</option>
  <option value=""{if $export_data.export_images eq '' and $export_data} selected="selected"{/if}>{$lng.lbl_no}</option>
  </select></td>
</tr>
</table>
