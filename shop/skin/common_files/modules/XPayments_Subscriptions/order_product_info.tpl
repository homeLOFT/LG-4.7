{*
04fde8f686ed0be7315b88835142e1a07fc30925, v1 (xcart_4_6_2), 2014-01-04 06:59:41, order_product_info.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
<tr>
  <td colspan="2"><b>{$lng.lbl_xps_subscription_info}</b></td>
</tr>
{foreach from=$product.extra_data.subscription.subscriptionids item=s}
{if $subscriptions_info[$s]}
{assign var=subscription_info value=$subscriptions_info[$s]}

<tr>
  <td colspan="2">&nbsp;</td>
</tr>

<tr>
  <td valign="top">
    <b>#{$s}</b>
    {if $subscription_info.status ne 'N'}
    <br/>
    <a href="orders.php?subscriptionid={$s}">{$lng.lbl_xps_find_orders}</a>
    {/if}
  </td>
  <td valign="top">

    {$lng.lbl_xps_subscription_status}: 
    <i>
    {if $subscription_info.status eq 'A'}
    {$lng.lbl_xps_active}
    {elseif $subscription_info.status eq 'S'}
    {$lng.lbl_xps_stopped}
    {elseif $subscription_info.status eq 'N'}
    {$lng.lbl_xps_not_created}
    {elseif $subscription_info.status eq 'F'}
    {$lng.lbl_xps_finished}
    {/if}
    </i>

    {if $subscription_info.status ne 'N' and $subscription_info.status ne 'F'}
    <br/>

    {if $usertype eq 'A' or ($usertype eq 'P' and $active_modules.Simple_Mode)}
    <form action="order.php" method="post" name="subscriptionstatus_{$s}">
      <input type="hidden" name="mode" value="update_subscription_status">
      <input type="hidden" name="orderid" value="{$orderid}">
      <input type="hidden" name="subscriptionid" value="{$s}">
      <input type="hidden" name="status" value="">
      {if $subscription_info.status eq 'A'}
      <input type="button" value="{$lng.lbl_xps_stop|strip_tags:false|escape}" onclick="document.subscriptionstatus_{$s}.status.value='S';submitForm(this, 'update_subscription_status')"/>
      {elseif $subscription_info.status eq 'S'}
      <input type="button" value="{$lng.lbl_xps_relaunch|strip_tags:false|escape}" onclick="document.subscriptionstatus_{$s}.status.value='A';submitForm(this, 'update_subscription_status')"/>
      {/if}
    </form>
    {/if}

    {if $subscription_info.rebill_periods gt 1}
      <b>{$lng.lbl_xps_payments_left}:</b>
      {$subscription_info.rebill_periods-$subscription_info.success_attempts}
      <br/>
    {/if}
    <b>{$lng.lbl_xps_next_bill_date}:</b>
    {$subscription_info.real_next_date|date_format:$config.Appearance.date_format}
    {if $subscription_info.real_next_date ne $subscription_info.next_date}
      <br/>
      <i><b>{$lng.lbl_xps_planned_bill_date}:</b>
      {$subscription_info.next_date|date_format:$config.Appearance.date_format}</i>
    {/if}

    {/if}

  </td>
</tr>
{/if}
{/foreach}
