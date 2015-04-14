{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, payment_giftcert.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
<ul>
  <li class="single-field">
    {capture name=regfield}
      <input type="text" size="32" id="gcid" name="gcid" />
      {include file="customer/buttons/button.tpl" type="input" style="image" button_id="apply_gc_button"}
    {/capture}
    {include file="modules/One_Page_Checkout/opc_form_field.tpl" content=$smarty.capture.regfield name=$lng.lbl_gift_certificate field="gcid" required="Y"}
  </li>
</ul>
