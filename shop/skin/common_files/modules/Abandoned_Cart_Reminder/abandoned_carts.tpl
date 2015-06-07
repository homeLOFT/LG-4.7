{*
$Id$
vim: set ts=2 sw=2 sts=2 et:
*}

<script type="text/javascript" src="{$SkinDir}/js/reset.js"></script>
<script type="text/javascript" src="{$SkinDir}/modules/Abandoned_Cart_Reminder/func.js"></script>
<script type="text/javascript">
//<![CDATA[
var searchform_def = [
{foreach from=$abcr_default_search_params item=item key=key}
  ['{$key}','{$item}'],
{/foreach}
];
//]]>
</script>

<h1>{$lng.lbl_abcr_abandoned_carts}</h1>
{capture name=dialog}

<h2>{$lng.lbl_abcr_search_for_abandoned_carts}</h2>
<form name="searchform" action="abandoned_carts.php" method="post">
<table cellpadding="1" cellspacing="5">

<tr>
  <td id="abcr-search-by-date" class="FormButton" nowrap="nowrap">{$lng.lbl_abcr_search_by_date}:</td>
  <td id="abcr-search-date-selection" colspan="3">
    <table>
      <tr>
        <td>{$lng.lbl_from}: {include file="main/datepicker.tpl" name="start_time" date=$abcr_search_prefilled.start_time}</td>
        <td>{$lng.lbl_to}: {include file="main/datepicker.tpl" name="end_time" date=$abcr_search_prefilled.end_time}</td>
      </tr>
    </table>
  </td>
</tr>

<tr>
  <td id="abcr-search-by-email" class="FormButton" nowrap="nowrap">{$lng.lbl_abcr_search_by_email_or_login}:</td>
  <td><input type="text" name="abcr_pattern" size="30" id="abcr-search-pattern" value="{$abcr_search_prefilled.abcr_pattern|escape}" /></td>
  <td class="abcr-submit-search"><input type="submit" value="{$lng.lbl_search|strip_tags:false|escape}" /></td>
  <td><a href="javascript:void(0);" onclick="javascript: reset_form('searchform', searchform_def);">{$lng.lbl_reset_filter}</a></td>
</tr>

</table>
</form>

<h2>{$lng.lbl_abcr_abandoned_carts}</h2>
<form name="abandoned_carts_form" action="abandoned_carts.php" method="post"> 
  <input type="hidden" name="mode" value="none" />
  <input type="hidden" name="abcr_create_order_email" id="abcr-create-order-email" value="" />

  <table cellpadding="2" cellspacing="1" id="abcr-search-results-table">
  <tr class="TableHead">
    <td>&nbsp;</td>
    <td>{$lng.lbl_email}</td>
    <td>{$lng.lbl_profile}</td>
    <td class="abcr-product-column">{$lng.lbl_products}</td>
    <td>Subtotal</td>
    {if $single_mode}
    <td>{$lng.lbl_coupon}</td>
    {/if}
    <td>{$lng.lbl_date}</td>
    {if $active_modules.Advanced_Order_Management and ($usertype eq 'A' or $usertype eq 'P' and $active_modules.Simple_Mode or $single_mode)}
      <td>{$lng.lbl_abcr_create_order}</td>
    {/if}
    <td>{$lng.lbl_abcr_is_notified}</td>
  </tr>


{if $abandoned_carts}
  {include file="main/check_all_row.tpl" style="line-height: 170%;" form="abandoned_carts_form" prefix="abandoned_carts"}
  {include file="main/navigation.tpl"}
  <br />

  {foreach from=$abandoned_carts item=cart}
  <tr class="{if $cart.notification_count gt 0}abcr-notified-cart {/if}abcr-search-result{cycle values=', TableSubHead'}">
    <td><input type="checkbox" name="abandoned_carts[{$cart.email}]"/></td>
    <td>{$cart.email}</td>
    <td class="abcr-profile-column">
      {if $cart.customer_info.firstname ne ''}{$cart.customer_info.firstname}{/if}{if $cart.customer_info.lastname ne ''} {$cart.customer_info.lastname}{/if}
      {if $cart.userid}
        <a href="user_modify.php?user={$cart.userid}&usertype=C">{if $cart.customer_info.firstname ne '' or $cart.customer_info.lastname ne ''}({/if}{$cart.login}{if $cart.customer_info.firstname ne '' or $cart.customer_info.lastname ne ''}){/if}</a>
      {/if}
      {if $cart.customer_info.address and ($config.Abandoned_Cart_Reminder.abcr_show_address eq 'Y' or ($config.Abandoned_Cart_Reminder.abcr_show_address eq 'A' and not $cart.userid))}
        <br />
        {foreach from=$address_types item=address_type}
          {assign var="address" value=$cart.customer_info.address.$address_type}
          <div class="address-box">
            <div class="address-section">
              {if $address_type eq 'B'}{$lng.lbl_billing_address}{else}{$lng.lbl_shipping_address}{/if}
            </div>

            <div class="address-line">
              {if $default_fields.title and $address.title ne ''}{$address.title|escape} {/if}
              {if $default_fields.firstname and $address.firstname ne ''}{$address.firstname|escape} {/if}
              {if $default_fields.lastname and $address.lastname ne ''}{$address.lastname|escape}{/if}
            </div>

            <div class="address-line">
              {if $default_fields.address and $address.address ne ''}{$address.address|escape},<br />{/if}
              {if $default_fields.address_2 and $address.address_2 ne ''}{$address.address_2|escape},<br />{/if}
              {if $default_fields.city and $address.city ne ''}{$address.city|escape}, {/if}
              {if $default_fields.state and $address.state ne ''}{$address.statename|default:$address.state|escape}, {/if}
              {if $default_fields.county and  $address.county ne ''}{$address.countyname|default:$address.county|escape}, <br />{/if}
              {if $default_fields.zipcode and $address.zipcode ne ''}{include file="main/zipcode.tpl" val=$address.zipcode zip4=$address.zip4 static=true}<br />{/if}
              {if $default_fields.country and $address.country ne ''}{$address.countryname|default:$address.country|escape}{/if}
            </div>

            <div class="address-line">
              {if $default_fields.phone and $address.phone ne ''}{$lng.lbl_phone}: {$address.phone|escape}{/if}<br />
              {if $default_fields.fax and $address.fax ne ''}{$lng.lbl_fax}: {$address.fax|escape}{/if}
            </div>

            {if $additional_fields ne ''}
              <div class="address-line">
                {foreach from=$additional_fields item=field}
                  {if $field.avail eq 'Y' and $field.section eq 'B'}
                    {if $additional_fields_type ne ''}
                      {assign var='field_value' value=$field.value.$additional_fields_type}
                    {else}
                      {assign var='field_value' value=$field.value}
                    {/if}
                    {if $field_value ne ''}
                      {$field.title}: {if $field.type ne 'C'}{$field_value|escape}{else}{if $field_value eq 'Y'}{$lng.lbl_yes}{else}{$lng.lbl_no}{/if}{/if}<br />
                    {/if}
                  {/if}
                {/foreach}
              </div>
            {/if}
          </div>
        {/foreach}
      {/if}
    </td>
    <td class="abcr-product-column">
      {* Products *}
      {foreach from=$cart.products_detailed item=product name=abcr_products_loop}
      <div class="abcr-product-list">
        {math equation="x + 1" x=$smarty.foreach.abcr_products_loop.index}. {if not $product.not_owned}<a href="{if $usertype eq 'A' or $single_mode}{$catalogs.admin}{else}{$catalogs.provider}{/if}/product_modify.php?productid={$product.productid}" title="{$product.product}">{/if}{$product.product|truncate:60}{if not $product.not_owned}</a>{if $product.amount gt 1}&nbsp;x&nbsp;{$product.amount}{/if}{/if}<br />
        {if $product.options}
          {$lng.lbl_selected_options}:<br />
          {foreach from=$product.options item=option}
            &nbsp;&nbsp;&nbsp;&nbsp;{$option.class}:&nbsp;{$option.option_name}<br />
          {/foreach}
        {/if}
      </div>  
      <br />
      {/foreach}
    </td>
    <td>{currency value=$cart.subtotal}</td>
    {if $single_mode}
    <td>{if $cart.coupon_data and $cart.coupon_data.coupon}<a href="{$catalogs.provider}/coupons.php">{$cart.coupon_data.coupon}{else}-{/if}</a></td>
    {/if}
    <td>{$cart.time|date_format:$config.Appearance.datetime_format}</td>
    {if $active_modules.Advanced_Order_Management and ($usertype eq 'A' or $usertype eq 'P' and $active_modules.Simple_Mode or $single_mode)}
    <td>
      {* Create order *}
      <input type="button" value="{$lng.lbl_abcr_create_order}" onclick="javascript: abcrPopulateOrderParams('{$cart.email}', document.abandoned_carts_form); submitForm(document.abandoned_carts_form, 'create_order');" />
    </td>
    {/if}
    <td>{if $cart.notification_count gt 0}<strong>{$cart.notification_count} {if $cart.notification_count eq 1}{$lng.lbl_abcr_time}{else}{$lng.lbl_abcr_times}{/if}</strong> ({$lng.lbl_abcr_last}: {$cart.notification_time|date_format:$config.Appearance.date_format}){else}<strong>{$lnb.lbl_no}</strong>{/if}</td>
  </tr>  
  {/foreach}
  </table>
  <br />
  {include file="main/navigation.tpl"}
  <br />
  <input type="button" value="{$lng.lbl_abcr_send_notifications_to_carts}" onclick="javascript: if (checkMarks(this.form, email_pattern_regexp) &amp;&amp; confirm('{$lng.lbl_abcr_are_you_sure_to_send_notifications_to_carts}')) {ldelim}submitForm(document.abandoned_carts_form, 'notify');{rdelim}" />
  {if $single_mode}
    <input type="checkbox" id="abcr-attach-coupon-group" name="abcr_attach_coupon_group" onchange="javascript: abcrToggleCouponParamsTable('group');" value="off" /><label for="abcr-attach-coupon-group">{$lng.lbl_abcr_attach_coupon}</label><br />
    <div id="abcr-coupon-params-group" style="display: none;">
      &nbsp;<span id="abcr-coupon-params-header">{$lng.lbl_abcr_coupon_parameters}</span>:<br />
      &nbsp;{$lng.lbl_coupon_type}:&nbsp;
      <select name="abcr_coupon_type_group" onchange="javascript: $('#discount_box').toggle($(this).val() != 'free_ship');">
        <option value="absolute">{$config.General.currency_symbol} {$lng.lbl_coupon_type_absolute|wm_remove|escape}</option>
        <option value="percent">{$lng.lbl_coupon_type_percent}</option>
        <option value="free_ship">{$lng.lbl_coupon_freeshiping}</option>
      </select>
      <span id="discount_box">&nbsp;{$lng.lbl_coupon_discount}:&nbsp;<input type="text" size="10" name="abcr_coupon_value_group" value="0.00"/></span>&nbsp;{$lng.lbl_abcr_expiration_date}&nbsp;{include file="main/datepicker.tpl" name="abcr_coupon_expire_group" date=$group_expiration_date}
    </div>
  {else}
    <br />
  {/if}
  <br />
  <input type="button" value="{$lng.lbl_delete_selected|strip_tags:false|escape}" onclick="javascript: if (checkMarks(this.form, email_pattern_regexp) &amp;&amp; confirm('{$lng.lbl_abcr_are_you_sure_to_delete_abandoned_carts}')) submitForm(document.abandoned_carts_form, 'delete');" />
{else} 
{* No abandoned carts were found *}
    <tr><td colspan="8" align="center"><br />{$lng.lbl_abcr_no_abandoned_carts_found}</td></tr>
  </table>
{/if}
</form>
{/capture}
{include file="customer/dialog.tpl" title=$lng.lbl_abcr_abandoned_carts content=$smarty.capture.dialog noborder=true}
