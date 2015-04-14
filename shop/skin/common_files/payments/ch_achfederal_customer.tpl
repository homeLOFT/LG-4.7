{*
1c2b6ebe99b971f290d0d402b94e84559baa9311, v1 (xcart_4_5_2), 2012-07-13 15:28:01, ch_achfederal_customer.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{if $hide_header ne "Y"}
  <a name="chinfo"></a>
  <h3>{$lng.lbl_check_information}</h3>
{/if}

{if $config.General.checkout_module eq 'One_Page_Checkout'}
<ul>
  <li class="single-field">
    {capture name=regfield}
      <select name="check_type">
        <option value="27">{$lng.lbl_achfederal_debut_checkking_account}</option>
        <option value="37">{$lng.lbl_achfederal_debut_saving_account}</option>
      </select>
    {/capture}
    {include file="modules/One_Page_Checkout/opc_form_field.tpl" content=$smarty.capture.regfield required="Y" name=$lng.lbl_achfederal_transaction_type field="check_type"}
  </li>
  <li class="single-field">
    {capture name=regfield}
      <input type="text" id="check_name" name="check_name" size="32" maxlength="128" value="{if $userinfo.lastname ne ""}{$userinfo.firstname} {$userinfo.lastname}{/if}" />
    {/capture}
    {include file="modules/One_Page_Checkout/opc_form_field.tpl" content=$smarty.capture.regfield required="Y" name=$lng.lbl_ch_name field="check_name"}
  </li>
  <li class="single-field">
    {capture name=regfield}
      <input type="text" id="check_ban" name="check_ban" size="32" maxlength="32" value="" />
    {/capture}
    {include file="modules/One_Page_Checkout/opc_form_field.tpl" content=$smarty.capture.regfield required="Y" name=$lng.lbl_ch_bank_account field="check_ban"}
  </li>
  <li class="single-field">
    {capture name=regfield}
      <input type="text" id="check_brn" name="check_brn" size="32" maxlength="32" value="" />
    {/capture}
    {include file="modules/One_Page_Checkout/opc_form_field.tpl" content=$smarty.capture.regfield required="Y" name=$lng.lbl_ch_bank_routing field="check_brn"}
  </li>
</ul>
{else}
<table cellspacing="0" class="data-table">
{if $hide_header ne "Y"}
<tr>
  <td class="register-section-title" colspan="3">
  <a name="chinfo"></a>
  <label>{$lng.lbl_check_information}</label>
  </td>
</tr>
{/if}
<tr>
  <td class="data-name"><label for="check_name">{$lng.lbl_achfederal_transaction_type}</label></td>
  <td class="data-required">*</td>
  <td>
      <select name="check_type">
        <option value="27">{$lng.lbl_achfederal_debut_checkking_account}</option>
        <option value="37">{$lng.lbl_achfederal_debut_saving_account}</option>
      </select>
  </td>
</tr>
<tr>
  <td class="data-name"><label for="check_name">{$lng.lbl_ch_name}</label></td>
  <td class="data-required">*</td>
  <td><input type="text" name="check_name" id="check_name" size="32" maxlength="128" value="{if $userinfo.lastname ne ""}{$userinfo.firstname} {$userinfo.lastname}{/if}" /></td>
</tr>
<tr>
  <td class="data-name"><label for="check_ban">{$lng.lbl_ch_bank_account}</label></td>
  <td class="data-required">*</td>
  <td><input type="text" name="check_ban" id="check_ban" size="32" maxlength="32" value="" /></td>
</tr>
<tr>
  <td class="data-name"><label for="check_brn">{$lng.lbl_ch_bank_routing}</label></td>
  <td class="data-required">*</td>
  <td><input type="text" name="check_brn" id="check_brn" size="32" maxlength="32" value="" /></td>
</tr>
</table>
{/if}
