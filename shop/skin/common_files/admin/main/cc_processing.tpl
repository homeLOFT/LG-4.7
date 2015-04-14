{*
16fb71fefa8aab5678eb4057a41c4de68b748bf6, v18 (xcart_4_6_1), 2013-09-11 13:33:31, cc_processing.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{$lng.txt_cc_ach_processing_top_text}

<br />
<br />

{capture name=dialog}

  <a name="payment_gateways"></a>

  <form action="cc_processing.php" method="get" name="myform">
    <input type="hidden" name="mode" value="add" />
    <input type="hidden" name="subscribe" value="" />

    <table cellpadding="2" cellspacing="1" width="100%">

      <tr>
        <td colspan="3">{$lng.txt_credit_card_processor_note}</td>
      </tr>

      <tr>
        <td nowrap="nowrap"><strong>{$lng.lbl_your_country}</strong></td>
        <td align="center" width="100%">
          <select name="payment_country" style="width: 100%" onChange="change_payment_country(); change_payment_banner(); return false;">
            <option value="ALL">{$lng.lbl_all_countries}</option>
           {if $selected_payment_country}
           <option value="{$selected_payment_country.code}" selected="selected">{$selected_payment_country.name}</option>
           {/if}
            {foreach from=$payment_countries.countries key=current_code item=item}
              {if $current_code neq $selected_payment_country.code}
              <option value="{$current_code}">{$item}</option>
              {/if}
            {/foreach}
          </select>
        </td>
        <td>{include file="main/tooltip_js.tpl" text=$lng.txt_help_payments_filter id="tip_txt_help_payments_filter" alt_image="lamp.gif" type='img' width=400}</td>
      </tr>

      <tr>
        <td nowrap="nowrap"><strong>{$lng.lbl_payment_gateways}</strong></td>
        <td align="center" width="100%">
          {assign var=type value=""}
          <select name="processor" style="width: 100%;">
            <option value="">{$lng.lbl_select}...</option>
            {foreach from=$cc_modules item=module}
              {if $module.type eq "C" and $module.type ne $type}
                {if $type ne ""}</optgroup>{/if}
                {assign var=type value="C"}
                <optgroup label="--- {$lng.lbl_credit_card_processors} ---">

              {elseif $module.type eq "Z_via_xp" and $module.type ne $type}
                {if $type ne ""}</optgroup>{/if}
                {assign var=type value="Z_via_xp"}
                <optgroup label="--- {$lng.lbl_credit_card_processor_via_xp} ---">

              {elseif $module.type eq "H" and $module.type ne $type}
                {if $type ne ""}</optgroup>{/if}
                {assign var=type value="H"}
                <optgroup label="--- {$lng.lbl_check_processors} ---">

              {elseif $module.type eq "D" and $module.type ne $type}
                {if $type ne ""}</optgroup>{/if}
                {assign var=type value="D"}
                <optgroup label="--- {$lng.lbl_direct_debit_processor} ---">

              {elseif $module.type eq "P" and $module.type ne $type}
                {if $type ne ""}</optgroup>{/if}
                {assign var=type value="P"}
                <optgroup label="--- {$lng.lbl_ps_processors} ---">

              {elseif $module.type eq "X" and $module.type ne $type}
                {if $type ne ""}</optgroup>{/if}
                {assign var=type value="X"}
                <optgroup label="--- {$lng.lbl_xpc_xpayments_methods} ---">
              {/if}

              {if $module.type eq "X"}
                <option value="{$module.processor}_{$module.param01}">{$module.module_name}</option>
              {elseif $module.processor eq 'via_xp'}
                <option value="{$module.processor}">{$module.module_name}</option>
              {else}
                <option value="{$module.processor}">{$module.module_name}</option>
              {/if}
            {/foreach}
            </optgroup>
          </select>
        </td>
        <td nowrap="nowrap">
          {include file="buttons/button.tpl" button_title=$lng.lbl_add href="javascript: if (document.myform.processor.value == 'via_xp') popupOpen('popup_info.php?action=usexp','XPC_METHODS_HELP',`$smarty.ldelim`width:550,height:500`$smarty.rdelim`); else if (document.myform.processor.selectedIndex > 0) document.myform.submit();"}
        </td>
      </tr>

    </table>
  </form>
  {include file="admin/main/payment_countries_js.tpl"}
 
{/capture}
{include file="dialog.tpl" title=$lng.lbl_payment_gateways content=$smarty.capture.dialog extra='width="100%"'}
