{*
e29d845740cfab5b0da2599ff007d046b97b676c, v2 (xcart_4_6_2), 2014-01-09 10:03:17, register_address_info.tpl, random 
vim: set ts=2 sw=2 sts=2 et:
*}
{if $need_address_info}

  {if $hide_header eq ''}
    <tr>
      <td colspan="3" class="register-section-title">
        <div><label>{$lng.lbl_billing_address}</label></div>
      </td>
    </tr>
  {/if}
  {include file="customer/main/register_address_fields.tpl" default_fields=$address_fields address=$userinfo.address.B id_prefix='b_' name_prefix="address_book[B]" zip_section="billing" update_address_book='Y' address_type='B' personal_firstname=$userinfo.personal_firstname personal_lastname=$userinfo.personal_lastname}

  {if $config.Shipping.need_shipping_section eq 'Y'}

    {if $hide_header eq ''}
      <tr>
        <td class="register-section-title register-exp-section{if not $ship2diff} register-sec-minimized{/if}" colspan="3">
          <div>
            <label class="pointer" for="ship2diff">{$lng.lbl_ship_to_different_address}</label>
            <input type="checkbox" id="ship2diff" name="ship2diff" value="Y"{if $ship2diff} checked="checked"{/if} />
          </div>
        </td>
      </tr>
    {/if}

    </tbody>
    <tbody id="ship2diff_box">

    {include file="customer/main/register_address_fields.tpl" default_fields=$address_fields address=$userinfo.address.S id_prefix='s_' name_prefix="address_book[S]" zip_section="shipping" update_address_book='Y' address_type='S' personal_firstname=$userinfo.personal_firstname personal_lastname=$userinfo.personal_lastname}

    </tbody>
    <tbody>

  {/if}
{/if}
