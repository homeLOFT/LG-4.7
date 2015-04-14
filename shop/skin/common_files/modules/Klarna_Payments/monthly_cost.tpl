{*
71ae8da1574e2d17ed36dafb837750799af54781, v1 (xcart_4_6_0), 2013-04-05 15:51:07, monthly_cost.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{if $monthly_cost ne 0}
  {capture name="monthly_cost"}
    <div>
      {if $products_list ne 'Y'}
        <img src="https://cdn.klarna.com/public/images/{$config.Klarna_Payments.user_country|upper}/logos/v1/basic/{$config.Klarna_Payments.user_country|upper}_basic_logo_std_blue-black.png?width=70&eid={$config.Klarna_Payments.klarna_default_eid}.png" alt="Klarna factura"/><br />
      {/if}
      {if $active_modules.XMultiCurrency}
        {assign var="currency_symbol" value=$store_currency_symbol}
      {else}
        {assign var="currency_symbol" value=$config.General.currency_symbol}
      {/if}
   	  {$lng.lbl_klarna_monthly_cost|substitute:"cost":$monthly_cost:"currency":$currency_symbol}
     	<div id="{$elementid}{if $is_new_arrivals_products eq "Y"}_new{/if}">{include file="modules/Klarna_Payments/condition_js.tpl"}</div>
      {if $config.Klarna_Payments.user_country eq 'nl'}
        <img src="{$ImagesDir}/klarna_nl_account_banner.jpeg" width="130" alt="" />
      {/if}
    </div>
  {/capture}

  {if $tag eq 'tr'}
    <tr>
      <td colspan="3">
        {$smarty.capture.monthly_cost}
      </td>
    </tr>
  {elseif $tag eq 'table'}  
    <table cellspacing="0">
      <tr>
        <td class="total-name">
          {$smarty.capture.monthly_cost}
        </td>
      </tr>
    </table>
  {else}  
    {$smarty.capture.monthly_cost}
  {/if}  
{/if}
