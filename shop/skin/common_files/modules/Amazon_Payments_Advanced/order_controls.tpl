{*
ca6d4d775068e2aac0daac39cbaff45582b9cb6b, v1 (xcart_4_6_4), 2014-04-24 17:41:53, order_controls.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{if $order.extra.AmazonOrderReferenceId neq '' and ($order.status eq 'A' || $order.status eq 'P' || $order.status eq 'C' || $order.status eq 'Q')}
  <br />
  {include file="main/subheader.tpl" title=$lng.lbl_amazon_pa_order_avail_actions}

  <script type="text/javascript">
    var lbl_amazon_pa_confirm_capture = '{$lng.lbl_amazon_pa_confirm_capture|escape:javascript}';
    var lbl_amazon_pa_confirm_void = '{$lng.lbl_amazon_pa_confirm_void|escape:javascript}';
    var lbl_amazon_pa_confirm_refund = '{$lng.lbl_amazon_pa_confirm_refund|escape:javascript}';
  </script>

  <form action="amazon_pa_order.php" name="amazon_pa_form" method="post">
  <input type="hidden" name="mode" value="" />
  <input type="hidden" name="orderid" value="{$order.orderid}" />

  <table cellspacing="0" cellpadding="2" class="ButtonsRow">
  <tr>

  {if $order.status eq 'A'}
  <td class="ButtonsRow">
    {if $order.extra.amazon_pa_capture_status eq ''}
      {assign var="btn_title" value="`$lng.lbl_capture` `$config.General.currency_symbol``$order.total`"}
      {include file="buttons/button.tpl" button_title=$btn_title href="javascript: if(confirm(lbl_amazon_pa_confirm_capture)) submitForm(document.amazon_pa_form, 'capture');"}
    {else}
      {$lng.lbl_amazon_pa_capture_status}: <b>{$order.extra.amazon_pa_capture_status}</b>
    {/if}
  </td>

  <td class="ButtonsRow">
    {include file="buttons/button.tpl" button_title=$lng.lbl_void href="javascript: if(confirm(lbl_amazon_pa_confirm_void)) submitForm(document.amazon_pa_form, 'void');"}
  </td>
  {/if} {* status eq A *}

  {if $order.status eq 'P' || $order.status eq 'C'}
  <td class="ButtonsRow">
    {if $order.extra.amazon_pa_refund_status eq ''}
      {assign var="btn_title" value="`$lng.lbl_refund` `$config.General.currency_symbol``$order.total`"}
      {include file="buttons/button.tpl" button_title=$btn_title href="javascript: if(confirm(lbl_amazon_pa_confirm_refund)) submitForm(document.amazon_pa_form, 'refund');"}
    {else}
      {$lng.lbl_amazon_pa_refund_status}: <b>{$order.extra.amazon_pa_refund_status}</b>
      &nbsp;(<a href="javascript:void(0);" onclick="javascript: submitForm(document.amazon_pa_form, 'refresh_refund_status');">{$lng.lbl_amazon_pa_refresh}</a>)
    {/if}
  </td>
  {/if}

  {if $order.status eq 'Q'}
  <td class="ButtonsRow">
    {include file="buttons/button.tpl" button_title=$lng.lbl_amazon_pa_refresh href="javascript: submitForm(document.amazon_pa_form, 'refresh');"}
  </td>
  {/if}

  </tr>

  </table>
  </form>
{/if}
