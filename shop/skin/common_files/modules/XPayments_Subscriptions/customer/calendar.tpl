{*
04fde8f686ed0be7315b88835142e1a07fc30925, v1 (xcart_4_6_2), 2014-01-04 06:59:41, calendar.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
<script type="text/javascript" src="{$SkinDir}/modules/XPayments_Subscriptions/customer/calendar.js"></script>

{capture name=dialog}
<div>

  <h1>{$subscription.product.product}</h1>

  <div class="subscription-legend ui-datepicker-inline ui-datepicker ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
    <div class="ui-datepicker-group">
      <table class="ui-datepicker-calendar">
        <tr>
          <td class="subscription-done"><a class="ui-state-default" href="javascript: void(0);">&nbsp;</a></td>
          <td>{$lng.lbl_xps_succcessfully_billed}</td>
        </tr>
        <tr>
          <td class="subscription-failed"><a class="ui-state-default" href="javascript: void(0);">&nbsp;</a></td>
          <td>{$lng.lbl_xps_failed_attempt}</td>
        </tr>
        <tr>
          <td class="subscription-pending"><a class="ui-state-default" href="javascript: void(0);">&nbsp;</a></td>
          <td>{$lng.lbl_xps_scheduled_payment}</td>
        </tr>
      </table>
    </div>
  </div>

  <div class="calendar">
    <div class="subscription-calendar" data-dates="{$subscription.dates}"></div>
  </div>

</div>
{/capture}
{include file="customer/dialog.tpl" title=$lng.lbl_xps_subscriptions content=$smarty.capture.dialog noborder=true}
