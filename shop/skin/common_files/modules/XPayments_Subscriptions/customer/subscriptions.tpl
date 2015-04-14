{*
5e9713d172e2c9d249170e826dbebb812d2b8e54, v3 (xcart_4_6_2), 2014-01-10 13:34:30, subscriptions.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
<h1>{$lng.lbl_xps_subscriptions}</h1>

{capture name=dialog}
<div class="subscriptions">

{if $subscriptions}
<table cellspacing="0" class="item width-100" summary="">
  {foreach from=$subscriptions item=s}

  <tr>
    <td colspan="2">
      <hr/>
    </td>
  </tr>

  <tr>
    <td class="image">
    {include file="product_thumbnail.tpl" productid=$s.product.image_id image_x=$s.product.image_x image_y=$s.product.image_y product=$s.product.product tmbn_url=$s.product.image_url id="product_thumbnail" type=$s.product.image_type}
    </td>
    <td class="details">
      <a href="product.php?productid={$s.productid}" class="product-product">{$s.product.product}</a>
      <div class="subscription-status">
        <span class="status">{$lng.lbl_xps_subscription_status}:</span>
        <span class="status-value">
          {if $s.status eq 'A'}
          <span class="active">{$lng.lbl_xps_active}</span>
          {elseif $s.status eq 'S'}
          <span class="stopped">{$lng.lbl_xps_stopped}</span>
          {elseif $s.status eq 'F'}
          <span class="finished">{$lng.lbl_xps_finished}</span>
          {/if}
        </span>
      </div>

      <div class="descr">{$s.product.descr}</div>

      {if $product.product_options ne ""}
        <p class="poptions-title">{$lng.lbl_selected_options}:</p>
        <div class="poptions-list">
          {include file="modules/Product_Options/display_options.tpl" options=$s.product.product_options}
        </div>
      {/if}

      <div class="subscription products">
        <span class="price">{$lng.lbl_xps_subscription_fee}:</span>
        <span class="price-value">{currency value=$s.fee}</span>
        <span class="market-price">{alter_currency value=$s.fee}</span>
        <br/><span>{$s.desc}</span>
        {if $s.status ne 'F'}
          {if $s.rebill_periods gt 1}
          <br/>{$lng.lbl_xps_payments_left}: {$s.rebill_periods-$s.success_attempts}
          {/if}
          <br/>{$lng.lbl_xps_date_of_next_payment}: {$s.real_next_date|date_format:$config.Appearance.date_format}
        {/if}
        <br/><a href="orders.php?subscriptionid={$s.subscriptionid}">{$lng.lbl_xps_find_orders}</a>
        <br/>
      </div>

      <div class="calendar">
        <span class="popup-link">
          <a href="xps_subscriptions.php?subscriptionid={$s.subscriptionid}" onclick="javascript: popupOpen('xps_subscriptions.php?subscriptionid={$s.subscriptionid}'); return false;" title="{$lng.lbl_xps_show_calendar|escape}">{$lng.lbl_xps_show_calendar}</a>
        </span>
      </div>
    </td>
  </tr>

  {/foreach}
</table>
{else}
{$lng.lbl_xps_no_subscriptions}
{/if}

</div>
{/capture}
{include file="customer/dialog.tpl" title=$lng.lbl_xps_subscriptions content=$smarty.capture.dialog noborder=true}
