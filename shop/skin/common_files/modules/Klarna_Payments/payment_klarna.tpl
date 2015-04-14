{*
71ae8da1574e2d17ed36dafb837750799af54781, v1 (xcart_4_6_0), 2013-04-05 15:51:07, payment_klarna.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
<ul>
  {if $config.Klarna_Payments.user_country eq 'nl' and $payment.processor eq "cc_klarna_pp.php"}
  <li>
      {if $active_modules.XMultiCurrency}
        {assign var="currency_symbol" value=$store_currency_symbol}
      {else}
        {assign var="currency_symbol" value=$config.General.currency_symbol}
      {/if}

      {$lng.lbl_klarna_nl_account_note|substitute:"cost":$cart.monthly_cost:"currency":$currency_symbol}<br />
      <img src="{$ImagesDir}/klarna_nl_account_banner.jpeg" width="200" alt="" />
  </li>
  {/if}
  <li class="single-field">
    {capture name=regfield}
      <label for="user_ssn_{$payment.paymentid}"></label>
      {if $config.Klarna_Payments.user_country eq 'de' or $config.Klarna_Payments.user_country eq 'nl'}
        {include file="main/datepicker.tpl" name="user_ssn" id="user_ssn_`$payment.paymentid`" start_year="1930" dp_onchange="changeSSN(document.getElementById('user_ssn_`$payment.paymentid`').value);"}
      {else}
        <input type="text" size="32" name="user_ssn" value="{$cart.klarna_ssn}" id="user_ssn_{$payment.paymentid}" onchange="javascript: changeSSN(this.value);"/>
      {/if}
      {assign var="ssn_title" value=$lng.lbl_klarna_pno_ssn}
      {if $config.Klarna_Payments.user_country eq 'de' or $config.Klarna_Payments.user_country eq 'nl'}
        {assign var="ssn_title" value=$lng.lbl_klarna_date_of_birth}
      {/if}
      {if $config.Klarna_Payments.user_country eq 'se'}
        <br /><a href="javascript: void(0);" onclick="javascript: klarna_popup_address();">{$lng.lbl_klarna_get_address}</a>
      {/if}
    {/capture}
    {include file="modules/One_Page_Checkout/opc_form_field.tpl" content=$smarty.capture.regfield name=$ssn_title field="user_ssn_`$payment.paymentid`" required="Y"}
  </li>
  {if $config.Klarna_Payments.user_country eq 'de' or $config.Klarna_Payments.user_country eq 'nl'}
    <li class="single-field">
      {capture name=regfield}
        <label for="user_gender_{$payment.paymentid}"></label>
        <select name="user_gender_{$payment.paymentid}" id="user_gender_{$payment.paymentid}" onchange="javascript: changeGender(this.value);">
          <option value='' selected="selected">{$lng.lbl_select_one}</option>
          <option value='1'>{$lng.lbl_klarna_male}</option>
          <option value='0'>{$lng.lbl_klarna_female}</option>
        </select><br />
      {/capture}
      {include file="modules/One_Page_Checkout/opc_form_field.tpl" content=$smarty.capture.regfield name=$lng.lbl_select_gender field="user_gender_`$payment.paymentid`" required="Y"}
    </li>
  {/if}
  <li class="single-field">
    {capture name=regfield}
      {if $payment.processor eq "cc_klarna_pp.php" and $klarna_pclasses ne ''} 
        <table>
          <tr style="display: none;">
            <td colspan="2"><input type="hidden" name="selected_pclass" id="place_selected_pclass" value="{$selected_pclass}" /></td>
          </tr>
          {foreach from=$klarna_pclasses item=pclass}
          <tr>
            {if $klarna_pclasses_count gt 1}
              <td><input type="radio" name="pclass" value="{$pclass.id}" id="pclass{$pclass.id}"{if $selected_pclass eq $pclass.id} checked="checked"{/if} onchange="javascript: changePClass();" /></td>
            {else}
              <td><input type="hidden" name="pclass" id="pclass{$pclass.id}" value="{$pclass.id}" /></td>
            {/if}
            <td><label for="pclass{$pclass.id}">
            {strip}
            {$pclass.description}&nbsp;-&nbsp; 
            {if $active_modules.XMultiCurrency}
              {$store_currency_symbol}
            {else}
              {$config.General.currency_symbol}
            {/if}
            {$pclass.monthly_cost}{$lng.lbl_klarna_per_month}<br /> 
            {/strip}
            </label></td>
          </tr>
          {/foreach}
        </table>
        
      {/if}
    {/capture}
    {include file="modules/One_Page_Checkout/opc_form_field.tpl" content=$smarty.capture.regfield}
  </li>
  {if $config.Klarna_Payments.user_country eq 'de'}
  <li class="single-field">
      <input type="checkbox" size="32" name="de_terms" value="Y" id="de_terms_{$payment.paymentid}" onclick="javascript: change_de_policy(this.checked)"/>
      <label for="de_terms_{$payment.paymentid}">{$lng.lbl_klarna_de_privacy_policy}</label>
  </li>
  {/if}
</ul>


