{*
$Id$
vim: set ts=2 sw=2 sts=2 et:
*}
<h1>{$lng.lbl_abcr_order_statistic}</h1>
{capture name=dialog}
<form action="abandoned_carts_statistic.php" name="filterform" method="post">
<table cellspacing="0" cellpadding="0" id="abcr-filter-order-statistic">
  <tr>
    <td nowrap="nowrap" class="abcr-header FormButton">{$lng.lbl_abcr_filter_by_date}:</td>
    <td class="abcr-start-date">{$lng.lbl_from}: {html_select_date prefix="abcr_start_" start_year=$abcr_start_year display_days=false time=$abcr_dates.start}</td>
    <td class="abcr-end-date">{$lng.lbl_to}: {html_select_date prefix="abcr_end_" start_year=$abcr_start_year display_days=false time=$abcr_dates.end}</td>
    <td><input type="submit" value="{$lng.lbl_search|strip_tags:false|escape}" /></td></td>    
  </tr>
</table>
</form>
<table cellpadding="2" cellspacing="1" id="abcr-order-statistic">
  <tr class="TableHead">
    <td>{$lng.lbl_date}</td>
    <td>{$lng.lbl_orders}</td>
    <td>{$lng.lbl_abcr_month_total}</td>
  </tr>
{foreach from=$recovering_statistic item=stat key=month}
  <tr{cycle values=', class="TableSubHead"'}>
    <td>{$month}</td>
    <td class="abcr-orders-cell">
    {foreach from=$stat.orders key=orderid item=total}
      <div class="abcr-order-entry"><a href="order.php?orderid={$orderid}">#{$orderid}</a>&nbsp;&nbsp;({currency value=$total})</div>
    {/foreach}
    </td>
    <td>{currency value=$stat.total}</td>
  </tr>
{/foreach}
<tr><td colspan="3" id="abcr-total">{$lng.lbl_abcr_total_revenue_recovered}: <strong>{currency value=$totals.total}</strong>
<br />{$lng.lbl_number_of_orders}: <strong>{$totals.orders}</strong></td></tr>
</table>
{/capture}
{include file="customer/dialog.tpl" title=$lng.lbl_abcr_order_statistic content=$smarty.capture.dialog noborder=true}
