{*
0a3dc9d9b0b4a57a400cf44664beaea8b4ce0341, v8 (xcart_4_7_0), 2015-01-26 17:22:03, orders_list.tpl, mixon

vim: set ts=2 sw=2 sts=2 et:
*}

{assign var="total" value=0.00}
{assign var="total_paid" value=0.00}

<script type="text/javascript">
//<![CDATA[
var txt_delete_selected_orders_warning = "{$lng.txt_delete_selected_orders_warning|wm_remove|escape:javascript|strip_tags}";
//]]>
</script>

{capture name=dialog}

  <div class="right-box">
    {include file="customer/buttons/button.tpl" button_title=$lng.lbl_search_again href="orders.php" style="link"}
  </div>

  {include file="customer/main/navigation.tpl"}

  {include file="customer/check_all_row.tpl" form="processorderform" prefix="orderids"}

  <form action="process_order.php" method="post" name="processorderform">
    <input type="hidden" name="mode" value="" />

    <table cellspacing="1" class="data-table width-100" summary="{$lng.lbl_search_results|escape}">

      <tr class="head-row">
        <th class="data-checkbox-column">&nbsp;</th>
        <th>{include file="customer/table_head_cell.tpl" url="orders.php?mode=search" column="orderid" title="#"}</th>
        <th>{include file="customer/table_head_cell.tpl" url="orders.php?mode=search" column="status" title=$lng.lbl_status}</th>
        {if $active_modules.AfterShip ne ''}
        <th>{include file="customer/table_head_cell.tpl" url="orders.php?mode=search" column="tracking" title=$lng.lbl_aftership_tracking_N}</th>
        {/if}
        <th>{include file="customer/table_head_cell.tpl" url="orders.php?mode=search" column="date" title=$lng.lbl_date}</th>
        <th>{include file="customer/table_head_cell.tpl" url="orders.php?mode=search" column="total" title=$lng.lbl_total}</th>
      </tr>

      {foreach from=$orders item=order name=orders}

        {inc value=$total inc=$order.total assign="total"}
        {if $order.status eq "P" or $order.status eq "C"}
          {inc value=$total_paid inc=$order.total assign="total_paid"}
        {/if}

        <tr{interline name=orders foreach_iteration="`$smarty.foreach.orders.iteration`" foreach_total="`$smarty.foreach.orders.total`"}>
          <td><input type="checkbox" name="orderids[{$order.orderid}]" /></td>
          <td><a href="order.php?orderid={$order.orderid}">#{$order.orderid}</a></td>
          <td>
            <a href="order.php?orderid={$order.orderid}"><strong>{include file="main/order_status.tpl" status=$order.status mode="static"}</strong></a>
            {if $active_modules.Stop_List and $order.blocked  eq 'Y'}
              <img src="{$ImagesDir}/spacer.gif" class="slist-no-ip" alt="{$lng.lbl_blocked|wm_remove}:{$order.ip}" title="{$lng.lbl_ip_blocked|substitute:"ip":$order.ip}" />
            {/if}
          </td>
          {if $active_modules.AfterShip ne ''}
          <td nowrap="nowrap" align="{if $order.tracking ne ""}right{else}center{/if}" width="20%">
            {include file="modules/AfterShip/aftership_tracking_customer.tpl" data_width="0" data_size="small" tracking_number=$order.tracking}
          </td>
          {/if}
          <td><a href="order.php?orderid={$order.orderid}">{$order.date|date_format:$config.Appearance.datetime_format}</a></td>
          <td class="data-right-column"><a href="order.php?orderid={$order.orderid}">{currency value=$order.total}</a></td>
        </tr>

      {/foreach}

      <tr>
        <td colspan="5" class="data-right-column">{$lng.lbl_gross_total}: <strong>{currency value=$total}</strong></td>
      </tr>

      <tr>
        <td colspan="5" class="data-right-column">{$lng.lbl_total_paid}: <strong>{currency value=$total_paid}</strong></td>
      </tr>

    </table>

    {include file="customer/main/navigation.tpl"}

    <div class="button-row">
      {include file="customer/buttons/button.tpl" button_title=$lng.lbl_invoices_for_selected href="javascript: if (!checkMarks(document.processorderform, new RegExp('orderids\[[0-9]+\]', 'gi'))) return false; document.processorderform.target = 'invoices'; submitForm(this, 'invoice'); document.processorderform.target = '';"}
    </div>

  </form>

{/capture}
{include file="customer/dialog.tpl" title=$lng.lbl_search_results content=$smarty.capture.dialog}
