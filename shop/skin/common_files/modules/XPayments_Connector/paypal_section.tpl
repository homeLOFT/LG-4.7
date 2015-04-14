{*
77bdbe583d8ced6209d5749c5a658177ddd7f410, v4 (xcart_4_6_1), 2013-09-03 16:35:56, paypal_section.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}

<tr class="first">
  <td rowspan="2">{$lng.txt_xpc_paypal_dp_equals_list}:</td>
  <td>
    {*Cardholder data must be collected in X-Payments: set to Y always*}
    <input type="hidden" name="{$conf_prefix}[use_xpc]" value="Y" />
    <select name="{$conf_prefix}[use_xpc_processor]">
      {if $xpc_data.warning eq 'no processor'}
        <option value="">{$lng.lbl_please_select_one}</option>
      {/if}
      {foreach from=$xpc_data.processors item=p}
        <option value="{$p.param01}"{if $p.selected} selected="selected"{/if}>{$p.module_name}</option>
      {/foreach}
    </select>
  </td>
</tr>

<tr class="comment">
  <td>{$lng.txt_xpc_paypal_dp_note}</td>
</tr>

<tr>
  <td>&nbsp;</td>
  <td>{$lng.txt_xpc_paypal_setup_recharges_note}</td>
</tr>  

{if $xpc_data.warning eq 'no configured'}
<tr>
  <td>&nbsp;</td>
  <td><strong>{$lng.lbl_warning}!</strong> {$lng.txt_xpc_paypal_dp_empty_warning}</td>
</tr>

{elseif $xpc_data.warning eq 'no equal'}
<tr>
  <td>&nbsp;</td>
  <td><strong>{$lng.lbl_warning}!</strong> {$lng.txt_xpc_paypal_dp_equal_warning}</td>
</tr>
{/if}
