{*
c3f2c63d6ec12340c11b3f3d5d6a44e80a517ccb, v5 (xcart_4_4_5), 2011-12-20 09:58:43, register_chinfo.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{if $checkout_module eq 'One_Page_Checkout'}
  
  {include file="modules/One_Page_Checkout/payment/ch_info.tpl" hide_header="Y"}

{else}
  
  <tr style="display: none;">
    <td>
<script type="text/javascript">
//<![CDATA[
requiredFields[requiredFields.length] = ['check_name','{$lng.lbl_ch_name|wm_remove|escape:javascript}'];
requiredFields[requiredFields.length] = ['check_ban','{$lng.lbl_ch_bank_account|wm_remove|escape:javascript}'];
requiredFields[requiredFields.length] = ['check_brn','{$lng.lbl_ch_bank_routing|wm_remove|escape:javascript}'];
{if $payment_cc_data.disable_ccinfo eq "N"}
requiredFields[requiredFields.length] = ['check_number','{$lng.lbl_ch_number|wm_remove|escape:javascript}'];
{/if}
//]]>
</script>
    </td>
  </tr>
  
  {if $hide_header ne "Y"}
    <tr>
      <td class="register-section-title" colspan="3">
        <div>
          <a name="chinfo"></a>
          <label>{$lng.lbl_check_information}</label>
        </div>
      </td>
    </tr>
  {/if}
  
  <tr>
    <td class="data-name"><label for="check_name">{$lng.lbl_ch_name}</label></td>
    <td class="data-required">*</td>
    <td><input type="text" id="check_name" name="check_name" size="32" maxlength="128" value="{if $userinfo.lastname ne ""}{$userinfo.firstname} {$userinfo.lastname}{/if}" /></td>
  </tr>
  
  <tr>
    <td class="data-name"><label for="check_ban">{$lng.lbl_ch_bank_account}</label></td>
    <td class="data-required">*</td>
    <td><input type="text" id="check_ban" name="check_ban" size="32" maxlength="32" value="" /></td>
  </tr>
  
  <tr>
    <td class="data-name"><label for="check_brn">{$lng.lbl_ch_bank_routing}</label></td>
    <td class="data-required">*</td>
    <td><input type="text" id="check_brn" name="check_brn" size="32" maxlength="32" value="" /></td>
  </tr>
  
  {if $payment_cc_data.disable_ccinfo eq "N"}
  
    <tr>
      <td class="data-name"><label for="check_number">{$lng.lbl_ch_number}</label></td>
      <td class="data-required">*</td>
      <td><input type="text" id="check_number" name="check_number" size="32" maxlength="32" value="" /></td>
    </tr>
  
  {/if}

{/if}
