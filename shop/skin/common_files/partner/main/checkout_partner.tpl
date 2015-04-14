{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, checkout_partner.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}

<tr>
  <td colspan="2">
    <div class="text-pre-block">
      {include file="customer/subheader.tpl" title=$lng.lbl_partner_id class="grey"}
      {$lng.txt_what_is_partner_id}
    </div>
  </td>
</tr>
<tr>
  <td class="data-name">{$lng.lbl_partner_id}:</td>
  <td>
    <input type="text" name="partner_id" />
  </td>
</tr>
