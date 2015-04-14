{*
c3f2c63d6ec12340c11b3f3d5d6a44e80a517ccb, v5 (xcart_4_4_5), 2011-12-20 09:58:43, register_ddinfo.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{if $hide_header ne "Y"}

  <tr>
    <td class="register-section-title" colspan="3">
      <div>
        <label>{$lng.lbl_check_information}</label>
      </div>
    </td>
  </tr>

{/if}

  <tr style="display: none;">
    <td>
<script type="text/javascript">
//<![CDATA[
requiredFields[requiredFields.length] = ['debit_name','{$lng.lbl_ch_name|wm_remove|escape:javascript}'];
requiredFields[requiredFields.length] = ['debit_bank_account','{$lng.lbl_ch_bank_account|wm_remove|escape:javascript}'];
requiredFields[requiredFields.length] = ['debit_bank_number','{$lng.lbl_ch_bank_routing|wm_remove|escape:javascript}'];
//]]>
</script>
    </td>
  </tr>

  <tr>
    <td class="data-name"><label for="debit_name">{$lng.lbl_ch_name}</label></td>
    <td class="data-required">*</td>
    <td><input type="text" id="debit_name" name="debit_name" size="32" maxlength="128" value="{if $userinfo.lastname ne ""}{$userinfo.firstname} {$userinfo.lastname}{/if}" /></td>
  </tr>

  <tr>
    <td class="data-name"><label for="debit_bank_account">{$lng.lbl_ch_bank_account}</label></td>
    <td class="data-required">*</td>
    <td><input type="text" id="debit_bank_account" name="debit_bank_account" size="32" maxlength="32" value="" /></td>
  </tr>

  <tr>
    <td class="data-name"><label for="debit_bank_number">{$lng.lbl_ch_bank_routing}</label></td>
    <td class="data-required">*</td>
    <td><input type="text" id="debit_bank_number" name="debit_bank_number"  size="32" maxlength="32" value="" /></td>
  </tr>

  <tr>
    <td class="data-name">{$lng.lbl_ch_bank_name}</td>
    <td class="data-required">&nbsp;</td>
    <td><input type="text" id="debit_bank_name" name="debit_bank_name" size="32" maxlength="32" value="" /></td>
  </tr>
