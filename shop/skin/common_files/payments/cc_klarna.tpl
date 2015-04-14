{*
71ae8da1574e2d17ed36dafb837750799af54781, v1 (xcart_4_6_0), 2013-04-05 15:51:07, cc_klarna.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{if $hide_header ne "Y"}
<h3>Klarna payment</h3>
{$lng.txt_cc_configure_top_text}
{/if}
<p />
{capture name=dialog}
<center>
<form action="cc_processing.php?cc_processor={$smarty.get.cc_processor|escape:"url"}" method="post">

<div><b><a href="configuration.php?option=Klarna_Payments&right">{$lng.lbl_klarna_view_general_settings}</b></a></div>
<table cellspacing="10">

{if $module_data.processor eq "cc_klarna_pp.php"}
{if $config.Klarna_Payments.klarna_avail_countries ne ''}
{foreach from=$config.Klarna_Payments.klarna_avail_countries item=c}
{assign var="c_pclasses" value=$pclasses.$c.pclasses}
<tr{cycle values=', class="TableSubHead"'}>
<td>{$lng.lbl_klarna_default_campaign|substitute:"country":$pclasses.$c.countryname}:</td>
<td>
  {if $c_pclasses ne ''}
  <table width="100%" cellspacing="3">
    <tr>
      <th>{$lng.lbl_description}</th>
      <th>{$lng.lbl_klarna_months}</th>
      <th>{$lng.lbl_klarna_interest_rate}</th>
      <th>{$lng.lbl_klarna_start_fee}</th>
      <th>{$lng.lbl_klarna_invoice_fee}</th>
      <th>{$lng.lbl_klarna_min_summ}</th>
    </tr>  
  {foreach from=$c_pclasses item=class}
    <tr>
      <td>{$class.description}</td>
      <td align="center">{$class.months}</td>
      <td align="center">{$class.interest_rate}%</td>
      <td align="center">{$class.start_fee}</td>
      <td align="center">{$class.invoice_fee}</td>
      <td align="center">{$class.min_amount}</td>
    </tr>
  {/foreach}
  </table>
  {/if}
</td>
</tr>
{/foreach}
{/if}
{/if}

</table>
<p />
<input type="submit" value="{$lng.lbl_update|strip_tags:false|escape}" />
</form>
</center>
{/capture}
{include file="dialog.tpl" title=$lng.lbl_cc_settings content=$smarty.capture.dialog extra='width="100%"'}
