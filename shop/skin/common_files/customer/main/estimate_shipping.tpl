{*
94487e6881cf7a5154ff7307d2c3e7ca809682df, v3 (xcart_4_6_2), 2013-11-19 15:27:33, estimate_shipping.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{include file="check_zipcode_js.tpl"}
{include file="change_states_js.tpl"}

<form action="popup_estimate_shipping.php" method="post" name="userinfoform" {if $shipping_estimate_fields.zipcode.required eq 'Y'}onsubmit="javascript: return check_zip_code(this);"{/if}>
<input type="hidden" name="mode" value="change_address" />

  <table cellspacing="1" class="change-userinfo" cellpadding="3">

    {include file="customer/main/address_fields.tpl" address=$address name_prefix="change_userinfo" id_prefix='change_userinfo_' default_fields=$shipping_estimate_fields}

    <tr>
      <td align="center" colspan="3">
        {include file="customer/buttons/submit.tpl" type="input"}
      </td>
    </tr>

  </table>

</form>
