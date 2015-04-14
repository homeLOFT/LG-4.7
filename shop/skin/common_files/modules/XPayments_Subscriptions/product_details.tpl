{*
44c726a5f2719901cc8ad0f1101dcc0f40dd3c53, v3 (xcart_4_6_2), 2014-01-17 13:05:54, product_details.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
<tr>
  {if $geid ne ''}<td width="15" class="TableSubHead"><input type="checkbox" value="Y" name="xps_fields[subscription][subscription_product]" /></td>{/if}
  <td class="FormButton" nowrap="nowrap">{$lng.lbl_xps_subscription_product}:</td>
  <td class="ProductDetails">
    <script type="text/javascript">
      $(function () {ldelim}
        var lbl_xps_setup_fee = '{$lng.lbl_xps_setup_fee} <span class="Text">({$config.General.currency_symbol}):</span>';
        var priceLabel = $('input[name="price"]').closest('tr').children('.FormButton');
        var _lbl_price = priceLabel.html();
        {literal}
        var checkSetupFee = function (status) {
          if (status) {
            priceLabel.html(lbl_xps_setup_fee);
          } else {
            priceLabel.html(_lbl_price);            
          }
        } 
        $('input[name="subscription[subscription_product]"]').change(function () {
          $('.subscription_field').toggle($(this).is(':checked'));
          checkSetupFee($(this).is(':checked'));
        });
        $('.subscription_field').toggle($('input[name="subscription[subscription_product]"]').is(':checked'));
        checkSetupFee($('input[name="subscription[subscription_product]"]').is(':checked'));
        {/literal}
      {rdelim});
    </script>
    <input type="checkbox" name="subscription[subscription_product]"{if $product.subscription.subscription_product eq 'Y'} checked="checked"{/if} />
  </td>
</tr>

<tr class="subscription_field">
  {if $geid ne ''}<td width="15" class="TableSubHead"><input type="checkbox" value="Y" name="xps_fields[subscription][billing_period]" /></td>{/if}
  <td class="FormButton" nowrap="nowrap">{$lng.lbl_xps_billing_period}:</td>
  <td class="ProductDetails">

    <script type="text/javascript">
      var periods_E = {ldelim} // each
        'W': '{$lng.lbl_xps_week}',
        'M': '{$lng.lbl_xps_month}',
        'Y': '{$lng.lbl_xps_year}'
      {rdelim};
      var periods_D = {ldelim} // every
        'D': '{$lng.lbl_xps_days}',
        'W': '{$lng.lbl_xps_weeks}',
        'M': '{$lng.lbl_xps_months}',
        'Y': '{$lng.lbl_xps_years}'
      {rdelim};
      var selectedPeriod = '{$product.subscription.period}';
    </script>
    {load_defer file="modules/XPayments_Subscriptions/product_details.js" type="js"}

    <select name="subscription[type]" id="subscription_type">
      <option value="E"{if $product.subscription.type eq 'E'} selected="selected"{/if}>{$lng.lbl_xps_each}</option>
      <option value="D"{if $product.subscription.type eq 'D'} selected="selected"{/if}>{$lng.lbl_xps_every}</option>
    </select>

    <input id="subscription_number" type="text" name="subscription[number]" size="18" value="{$product.subscription.number}" />

    <span id="subscription_number_suffix">{$lng.lbl_xps_number_suffix}</span>

    <select  id="subscription_period" name="subscription[period]">
    </select>

    <br/>
    <span id="subscription_reverse_note">{$lng.lbl_xps_reverse_note}</span>

    <br/>
    <label id="subscription_reverse_label" for="subscription_reverse">
      <input type="checkbox" id="subscription_reverse" name="subscription[reverse]"{if $product.subscription.reverse eq 'Y'} checked="checked"{/if}/>
      {$lng.lbl_xps_reverse}
    </label>

  </td>
</tr>

<tr class="subscription_field">
  {if $geid ne ''}<td width="15" class="TableSubHead"><input type="checkbox" value="Y" name="xps_fields[subscription][fee]" /></td>{/if}
  <td class="FormButton" nowrap="nowrap">{$lng.lbl_xps_subscription_fee} <span class="Text">({$config.General.currency_symbol}):</span></td>
  <td class="ProductDetails">
    <input type="text" name="subscription[fee]" size="18" value="{$product.subscription.fee|formatprice|default:$zero}" />
  </td>
</tr>

<tr class="subscription_field">
  {if $geid ne ''}<td width="15" class="TableSubHead"><input type="checkbox" value="Y" name="xps_fields[subscription][rebill_periods]" /></td>{/if}
  <td class="FormButton" nowrap="nowrap">{$lng.lbl_xps_rebill_periods}:</td>
  <td class="ProductDetails">
    <input type="text" name="subscription[rebill_periods]" size="18" value="{$product.subscription.rebill_periods|default:0}" />
  </td>
</tr>
