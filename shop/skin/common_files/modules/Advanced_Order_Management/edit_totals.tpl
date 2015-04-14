{*
f7753843bded08de90c93873b4a2fb1ed4ccb574, v15 (xcart_4_7_0), 2015-03-04 09:39:41, edit_totals.tpl, mixon

vim: set ts=2 sw=2 sts=2 et:
*}
<script type="text/javascript">
//<![CDATA[
    var orderObject = {
        coupon_type: '{$cart_order.coupon_type}',
        shipping_cost: '{$cart_order.shipping_cost}',
        use_coupon_discount_alt: '{$cart_order.use_coupon_discount_alt}'
    };
    $(document).ready(function() {
        if (
            orderObject.coupon_type === 'free_ship'
            && parseFloat(orderObject.shipping_cost) === 0
            && orderObject.use_coupon_discount_alt !== 'Y'
        ) {
            $('form[name="edittotals_form"] [data-role=coupon]').parentsUntil('tbody', 'tr').next().hide();
        }
        if ((select = $('form[name="edittotals_form"] [name="total_details[coupon_discount_type_alt]"] [value="free_ship"]')).length > 0) {
            UpdateUI(select.parent());
        }
    });
    function MarkElement(element) {
        element = $(element);
        element.parentsUntil('tbody', 'tr').prev().find('input, select').prop('disabled', element.is(':checked'));
        element.nextAll('input, select').prop('disabled', !element.is(':checked'));
    }
    function UpdateUI(element) {
        element = $(element);
        var role = element.attr('data-role');
        switch (role) {
            case 'coupon':
                var coupon_type = element.find(':selected').attr('data-coupon-type');
                if (coupon_type === 'free_ship') {
                    element.parentsUntil('tbody', 'tr').next().hide();
                    $('input[name="total_details[shipping_cost_alt]"]').parentsUntil('tbody', 'tr').hide();
                    $('input[name="total_details[shipping_cost_alt]"]').parentsUntil('tbody', 'tr').prev().hide();
                } else {
                    element.parentsUntil('tbody', 'tr').next().show();
                    $('input[name="total_details[shipping_cost_alt]"]').parentsUntil('tbody', 'tr').show();
                    $('input[name="total_details[shipping_cost_alt]"]').parentsUntil('tbody', 'tr').prev().show();
                }
                if ((select = $('form[name="edittotals_form"] [name="total_details[coupon_discount_type_alt]"] [value="free_ship"]')).length > 0) {
                    UpdateUI(select.parent());
                }
                $('form[name="edittotals_form"] input[name="total_details[coupon_type]"]').val(coupon_type ? coupon_type : 'none');
                break;
            case 'coupon-type':
                var coupon_type = element.find(':selected').val();
                if (coupon_type === 'free_ship') {
                    element.prev('input').hide();
                    element.css('width', 'auto');
                } else {
                    element.prev('input').show();
                    element.css('width', '');
                }
                $('form[name="edittotals_form"] input[name="total_details[coupon_type]"]').val(coupon_type ? coupon_type : 'absolute');
                break;
        }
    }
//]]>
</script>

<style>
    select[name$="_type_alt]"] {
        width: 43px;
    }
</style>

{capture name=dialog}

    <form action="order.php" method="post" name="edittotals_form">
      <input type="hidden" name="mode" value="edit" />
      <input type="hidden" name="action" value="update_totals" />
      <input type="hidden" name="show" value="totals" />
      <input type="hidden" name="orderid" value="{$orderid}" />

      <input type="hidden" name="total_details[coupon_type]" value="{$cart_order.coupon_type}" />

      <table cellspacing="1" cellpadding="5" width="100%">

        <tr>
          <td colspan="3">{include file="main/subheader.tpl" title=$lng.lbl_order_info}</td>
        </tr>

        {if $config.Taxes.display_taxed_order_totals eq "Y"}
            <tr>
              <td colspan="3">{$lng.txt_taxed_order_totals_displayed}</td>
            </tr>
        {/if}

        <tr>
          <td colspan="3">{include file="customer/main/cart_details.tpl" cart=$cart_order}<br /></td>
        </tr>

        {if $shipping_lost}
            <tr>
              <td colspan="3">
                {assign var="t_ship_method" value=$orig_order.shipping|trademark:'use_alt'}
                <font class="ErrorMessage">{$lng.lbl_warning}!</font> {$lng.lbl_aom_unaccessible_shipmethod|substitute:"t_ship_method":$t_ship_method}
                <br /><br />
              </td>
            </tr>
        {/if}

        {if $config.Shipping.enable_shipping eq "Y" and $active_modules.UPS_OnLine_Tools and $config.Shipping.realtime_shipping eq "Y" and $config.Shipping.use_intershipper ne "Y" and $show_carriers_selector eq "Y"}
            <tr>
              <td colspan="3">
                <font class="FormButton">{$lng.lbl_aom_shipping_carrier}: </font>
                <select name="selected_carrier" onchange="document.edittotals_form.submit()">
                  <option value="UPS"{if $current_carrier eq "UPS"} selected="selected"{/if}>{$lng.lbl_ups_carrier}</option>
                  <option value=""{if $current_carrier ne "UPS"} selected="selected"{/if}>{$lng.lbl_other_carriers}</option>
                </select>
                <br /><br />
              </td>
            </tr>
        {/if}

        <tr class="TableHead">
          <td height="16">&nbsp;</td>
          <th height="16" align="left">{$lng.lbl_aom_current_value}</th>
          <th height="16" align="left">{$lng.lbl_aom_original_value}</th>
        </tr>

        <tr{cycle values=', class="TableSubHead"'}>
          <td height="18">{$lng.lbl_subtotal}</td>
          <td>
            {currency value=$cart_order.display_subtotal}
          </td>
          <td>
            {currency value=$orig_order.display_subtotal}
          </td>
        </tr>

        <tr{cycle values=', class="TableSubHead"' advance=false}>
          <td rowspan="2">{$lng.lbl_payment_method}</td>
          <td><input type="text" size="30" maxlength="50" name="total_details[payment_method]" value="{$cart_order.payment_method|escape}" {if $cart_order.use_payment_alt eq 'Y'}disabled="disabled"{/if} {include file="main/attr_orig_data.tpl" data_orig_value=$orig_order.payment_method data_orig_keep_empty='Y'} data-aom-related-ui-control="total_details[use_payment_alt]"/></td>
          <td rowspan="2">{$orig_order.payment_method}</td>
        </tr>
        <tr{cycle values=', class="TableSubHead"'}>
          <td>{$lng.lbl_other}:&nbsp;<input type="checkbox" name="total_details[use_payment_alt]" onclick="javascript: MarkElement(this)" {if $cart_order.use_payment_alt eq 'Y'}checked="checked"{/if} />
            <select name="total_details[payment_alt]" {if $cart_order.use_payment_alt ne 'Y'}disabled="disabled"{/if}>
              {section name=pm loop=$payment_methods}
                  <option value="{$payment_methods[pm].paymentid}:::{$payment_methods[pm].payment_method}"{if $payment_methods[pm].paymentid eq $cart_order.paymentid} selected="selected"{/if}>{$payment_methods[pm].payment_method} ({if $payment_methods[pm].surcharge_type eq '%'}{$payment_methods[pm].surcharge|formatprice}%{else}{currency value=$payment_methods[pm].surcharge}{/if})</option>
              {/section}
            </select></td>
        </tr>
        <tr class="TableSubHead" data-aom-payment-surcharge-amount>
          <td rowspan="2">{if $order.payment_surcharge gte 0}{$lng.lbl_payment_method_surcharge}{else}{$lng.lbl_payment_method_discount}{/if}</td>
          <td>
            <div>
              <div class="aom-calculator" title="{$lng.lbl_aom_calculated_value}"></div>
              <div class="aom-calculated">
                {currency value=$cart_order.payment_surcharge}
              </div>
            </div>
          </td>
          <td rowspan="2">{currency value=$orig_order.payment_surcharge}</td>
        </tr>
        <tr class="TableSubHead">
          <td>{$lng.lbl_other}:&nbsp;<input type="checkbox" name="total_details[use_payment_surcharge_alt]" onclick="javascript: MarkElement(this)"{if $cart_order.use_payment_surcharge_alt eq 'Y'} checked="checked"{/if} />
            <input type="text" size="12" maxlength="12" name="total_details[payment_surcharge_alt]" value="{if $cart_order.payment_surcharge_type_alt eq "percent"}{$cart_order.payment_surcharge_alt|formatprice}{else}{$cart_order.payment_surcharge|formatprice}{/if}"{if $cart_order.use_payment_surcharge_alt ne 'Y'} disabled="disabled"{/if} />
            <select name="total_details[payment_surcharge_type_alt]"{if $cart_order.use_payment_surcharge_alt ne 'Y'} disabled="disabled"{/if}>
              <option value="percent"{if $cart_order.payment_surcharge_type_alt eq "percent"} selected="selected"{/if}>%</option>
              <option value="absolute"{if $cart_order.payment_surcharge_type_alt eq "absolute"} selected="selected"{/if}>{$config.General.currency_symbol}</option>
            </select>
          </td>
        </tr>

        {if $shipping_calc_error ne ""}
            <tr class="TableHead">
              <td colspan="3">{$shipping_calc_service} {$lng.lbl_err_shipping_calc}<br /><font class="ErrorMessage">{$shipping_calc_error}</font>
            </tr>
        {/if}

        <tr{cycle values=', class="TableSubHead"' advance=false}>
          <td rowspan="2">{$lng.lbl_delivery}</td>
          <td><input type="text" size="30" maxlength="50" name="total_details[shipping]" value="{$cart_order.shipping|escape}" {if $cart_order.use_shipping_alt eq 'Y'}disabled="disabled"{/if} {include file="main/attr_orig_data.tpl" data_orig_value=$orig_order.shipping data_orig_keep_empty='Y'} data-aom-related-ui-control="total_details[use_shipping_alt]"/></td>
          <td rowspan="2">{$orig_order.shipping|trademark|default:$lng.lbl_aom_shipmethod_notavail}</td>
        </tr>
        <tr{cycle values=', class="TableSubHead"'}>
          <td>
            {if $shipping}
              {$lng.lbl_other}:&nbsp;<input type="checkbox" name="total_details[use_shipping_alt]" onclick="javascript: MarkElement(this)" {if $cart_order.use_shipping_alt eq 'Y'}checked="checked"{/if} />
              <select name="total_details[shipping_alt]" {if $cart_order.use_shipping_alt ne 'Y'}disabled="disabled"{/if}>
                {section name=sm loop=$shipping}
                    <option value="{$shipping[sm].shippingid}:::{$shipping[sm].shipping}"{if $shipping[sm].shippingid eq $cart_order.shippingid} selected="selected"{/if}>{$shipping[sm].shipping|trademark:"use_alt"}{if $config.Appearance.display_shipping_cost eq "Y"} ({currency value=$shipping[sm].rate plain_text_message=1}){/if}</option>
                {/section}
              </select>
            {else}
                {$lng.lbl_aom_shipmethod_notavail}
            {/if}
          </td>
        </tr>
        {if $config.Shipping.realtime_shipping eq "Y" and $config.Shipping.use_intershipper ne "Y" and (not $active_modules.UPS_OnLine_Tools or $show_carriers_selector ne 'Y' or $current_carrier ne 'UPS') and $dhl_ext_countries and $has_active_arb_smethods}
            <tr{cycle values=', class="TableSubHead"'}>
              <td height="18">{$lng.lbl_dhl_ext_country}</td>
              <td>
                <select name="dhl_ext_country" id="dhl_ext_country" {include file="main/attr_orig_data.tpl" data_orig_value=$orig_order.extra.dhl_ext_country data_orig_keep_empty='Y'}>
                  <option value="">{$lng.lbl_please_select_one}</option>
                  {foreach from=$dhl_ext_countries item=c}
                      <option value="{$c}"{if $c eq $dhl_ext_country} selected="selected"{/if}>{$c}</option>
                  {/foreach}
                </select>
              </td>
              <td>{$orig_order.extra.dhl_ext_country}</td>
            </tr>
        {/if}
        <tr data-aom-shipping-cost>
            <td>{$lng.lbl_shipping_cost}</td>
            <td>
              {if $order.coupon and $order.coupon_type eq "free_ship"}
                  {currency value=0}&nbsp;({$lng.lbl_free_ship_coupon_record|substitute:"code":$order.coupon})
              {else}
                  <div>
                    <div class="aom-calculator" title="{$lng.lbl_aom_calculated_value}"></div>
                    <div class="aom-calculated">
                      {currency value=$cart_order.display_shipping_cost}
                    </div>
                  </div>
              {/if}
            </td>
            <td>
              {if $orig_order.coupon and $orig_order.coupon_type eq "free_ship"}
                  {currency value=0}&nbsp;({$lng.lbl_free_ship_coupon_record|substitute:"code":$orig_order.coupon})
              {else}
                  {currency value=$orig_order.display_shipping_cost}
              {/if}
            </td>
        </tr>
        {if $cart_order.coupon_type ne "free_ship"}
            {assign var="note_shipping_cost" value="1"}
            <tr>
              <td></td>
              <td>{$lng.lbl_other}:&nbsp;<input type="checkbox" name="total_details[use_shipping_cost_alt]" value="Y"{if $cart_order.use_shipping_cost_alt eq "Y"} checked="checked"{/if} onclick="javascript:MarkElement(this)" />
                  <input type="text" size="15" maxlength="15" name="total_details[shipping_cost_alt]" value="{$cart_order.shipping_cost_alt|formatprice}"{if $cart_order.use_shipping_cost_alt eq ""} disabled="disabled"{/if} />
              </td>
              <td>&nbsp;</td>
            </tr>
        {/if}

        <tr{cycle values=', class="TableSubHead"' advance=false} data-aom-discount-amount>
          <td rowspan="2">{$lng.lbl_discount}</td>
          <td>
            <div>
              <div class="aom-calculator" title="{$lng.lbl_aom_calculated_value}"></div>
              <div class="aom-calculated">
                {currency value=$cart_order.discount}
              </div>
            </div>
          </td>
          <td rowspan="2">{currency value=$orig_order.discount}{if $orig_order.discount gt 0 and $orig_order.extra.discount_info.discount_type eq "percent"} ({$orig_order.extra.discount_info.discount}%){/if}</td>
        </tr>
        <tr{cycle values=', class="TableSubHead"'}>
          <td>{$lng.lbl_other}:&nbsp;<input type="checkbox" name="total_details[use_discount_alt]" onclick="javascript: MarkElement(this)"{if $cart_order.use_discount_alt eq 'Y'} checked="checked"{/if} />
            <input type="text" size="12" maxlength="12" name="total_details[discount_alt]" value="{$cart_order.extra.discount_info.discount|escape}"{if $cart_order.use_discount_alt ne 'Y'} disabled="disabled"{/if} />
            <select name="total_details[discount_type_alt]"{if $cart_order.use_discount_alt ne 'Y'} disabled="disabled"{/if}>
              <option value="percent"{if $cart_order.extra.discount_info.discount_type eq "percent"} selected="selected"{/if}>%</option>
              <option value="absolute"{if $cart_order.extra.discount_info.discount_type eq "absolute"} selected="selected"{/if}>{$config.General.currency_symbol}</option>
            </select>
          </td>
        </tr>

        <tr{cycle values=', class="TableSubHead"' advance=false} data-aom-coupon-discount-amount>
          <td>{$lng.lbl_coupon_saving}</td>
          <td>
            <div>
              <div class="aom-calculator" title="{$lng.lbl_aom_calculated_value}"></div>
              <div class="aom-calculated">
                {currency value=$cart_order.coupon_discount}
              </div>
            </div> (
            <select name="total_details[coupon_alt]"{if $cart_order.use_coupon_discount_alt eq 'Y' and not $cart_order.use_old_coupon_discount} disabled="disabled"{/if} data-role="coupon" onchange="javascript: UpdateUI(this);">
              <option value="">{$lng.lbl_none}</option>
              {foreach from=$coupons item=v}
                  <option value="{if $v.__deleted}__old_coupon__{else}{$v.coupon|escape}{/if}"{if $cart_order.coupon eq $v.coupon or ($cart_order.use_coupon_discount_alt eq 'Y' and not $cart_order.use_old_coupon_discount and $cart_order.__original_coupon eq $v.coupon)} selected="selected"{/if} data-coupon-type="{$v.coupon_type}">{$v.coupon} -{$v.discount}{if $v.coupon_type eq 'percent'}%{else}{$config.General.currency_symbol}{/if}{if $v.__deleted and $v.coupon_type neq "free_ship"} ({$lng.lbl_aom_coupon_not_found|wm_remove|escape}){/if}</option>
              {/foreach}
            </select>
            )
          </td>
          <td>{currency value=$orig_order.coupon_discount}{if $orig_order.coupon ne ""} ({$orig_order.coupon}){/if}</td>
        </tr>
        <tr{cycle values=', class="TableSubHead"'}>
          <td></td>
          <td>{$lng.lbl_other}:&nbsp;<input type="checkbox" name="total_details[use_coupon_discount_alt]" onclick="javascript: MarkElement(this)"{if $cart_order.use_coupon_discount_alt eq 'Y' and not $cart_order.use_old_coupon_discount} checked="checked"{/if} />
            <input type="text" size="12" maxlength="12" id="coupon_discount_alt" name="total_details[coupon_discount_alt]" value="{$cart_order.extra.discount_coupon_info.discount|escape}"{if $cart_order.use_coupon_discount_alt ne 'Y' or $cart_order.use_old_coupon_discount} disabled="disabled"{/if} />
            <select name="total_details[coupon_discount_type_alt]"{if $cart_order.use_coupon_discount_alt ne 'Y' or $cart_order.use_old_coupon_discount} disabled="disabled"{/if} data-role='coupon-type' onchange="javascript: UpdateUI(this);">
              <option value="absolute"{if $cart_order.extra.discount_coupon_info.coupon_type eq "absolute"} selected="selected"{/if}>{$config.General.currency_symbol}</option>
              <option value="percent"{if $cart_order.extra.discount_coupon_info.coupon_type eq "percent"} selected="selected"{/if}>%</option>
              <option value="free_ship"{if $cart_order.extra.discount_coupon_info.coupon_type eq "free_ship"} selected="selected"{/if}>{$lng.lbl_free_shipping}</option>
            </select>
          </td>
          <td>&nbsp;</td>
        </tr>

        {if $order.discounted_subtotal ne $order.subtotal}
            <tr{cycle values=', class="TableSubHead"'}>
              <td>{$lng.lbl_discounted_subtotal}</td>
              <td>{currency value=$cart_order.display_discounted_subtotal}</td>
              <td>{currency value=$orig_order.display_discounted_subtotal}</td>
            </tr>
        {/if}

        {if ($orig_order.applied_taxes or $cart_order.applied_taxes) and $config.Taxes.display_taxed_order_totals ne "Y"}
            <tr{cycle values=', class="TableSubHead"'}>
              <td>{$lng.lbl_taxes}</td>
              <td nowrap="nowrap">
                {foreach key=tax_name item=tax from=$cart_order.applied_taxes}
                    {currency value=$tax.tax_cost} [<small>{$tax.tax_display_name} ({$tax.formula}){if $tax.rate_type eq "%"} {include file="main/display_tax_rate.tpl" value=$tax.rate_value}%{/if}</small>]<br />
                {/foreach}
              </td>
              <td nowrap="nowrap">
                {foreach key=tax_name item=tax from=$orig_order.applied_taxes}
                    {currency value=$tax.tax_cost} [<small>{$tax.tax_display_name} ({$tax.formula}){if $tax.rate_type eq "%"} {include file="main/display_tax_rate.tpl" value=$tax.rate_value}%{/if}</small>]<br />
                {/foreach}
              </td>
            </tr>
        {/if}

        {if $order.giftcert_discount gt 0}
            <tr{cycle values=', class="TableSubHead"'}>
              <td class="LabelStyle" nowrap="nowrap">{$lng.lbl_giftcert_discount}</td>
              <td>{currency value=$cart_order.giftcert_discount}</td>
              <td>{currency value=$orig_order.giftcert_discount}</td>
            </tr>
        {/if}

        <tr{cycle values=', class="TableSubHead"'}>
          <td><b style="text-transform: uppercase;">{$lng.lbl_total}</b></td>
          <td><b>{currency value=$cart_order.total}</b></td>
          <td><b>{currency value=$orig_order.total}</b></td>
        </tr>

        {if ($orig_order.applied_taxes or $cart_order.applied_taxes) and $config.Taxes.display_taxed_order_totals eq "Y"}
            <tr>
              <td><b>{$lng.lbl_including}:</b></td>
              <td nowrap="nowrap">
                {foreach key=tax_name item=tax from=$cart_order.applied_taxes}
                    <b>{currency value=$tax.tax_cost}</b> [<small>{$tax.tax_display_name} ({$tax.formula}){if $tax.rate_type eq "%"} {include file="main/display_tax_rate.tpl" value=$tax.rate_value}%{/if}</small>]<br />
                {/foreach}
              </td>
              <td nowrap="nowrap">
                {foreach key=tax_name item=tax from=$orig_order.applied_taxes}
                    <b>{currency value=$tax.tax_cost}</b> [<small>{$tax.tax_display_name} ({$tax.formula}){if $tax.rate_type eq "%"} {include file="main/display_tax_rate.tpl" value=$tax.rate_value}%{/if}</small>]<br />
                {/foreach}
              </td>
            </tr>
        {/if}

        {section name=rn loop=$cart_order.reg_numbers}
            {if $smarty.section.rn.first}
                <tr{cycle values=', class="TableSubHead"' advance=false}>
                  <td valign="top" colspan="3" class="LabelStyle" nowrap="nowrap">{$lng.lbl_registration_number}:  </td>
                </tr>
            {/if}

            <tr{cycle values=', class="TableSubHead"'}>
              <td valign="top" colspan="3" nowrap="nowrap">&nbsp;&nbsp;{$cart_order.reg_numbers[rn]}</td>
            </tr>
        {/section}

        {if $cart_order.applied_giftcerts}
            <tr>
              <td colspan="3" height="14">&nbsp;</td>
            </tr>

            <tr class="TableHead">
              <td colspan="3" height="16" class="LabelStyle"><b>{$lng.lbl_applied_giftcerts}:</b></td>
            </tr>

            {section name=gc loop=$cart_order.applied_giftcerts}
                <tr{cycle values=', class="TableSubHead"'}>
                  <td>&nbsp;&nbsp;{$cart_order.applied_giftcerts[gc].giftcert_id}:</td>
                  <td>{currency value=$cart_order.applied_giftcerts[gc].giftcert_cost}</td>
                  <td>{currency value=$orig_order.applied_giftcerts[gc].giftcert_cost}</td>
                </tr>
            {/section}
        {/if}

        <tr>
          <td colspan="3"><br />
            <input type="submit" value="{$lng.lbl_update}" />
            <br /><br />
          </td>
        </tr>

      </table>
    </form>

    {if $note_shipping_cost ne ""}
        {$lng.lbl_aom_use_fixed_shipping_note}
        {if ($orig_order.applied_taxes or $cart_order.taxes) and $config.Taxes.display_taxed_order_totals eq "Y"}
            <br />
            {$lng.lbl_aom_use_fixed_shipping_note2}
        {/if}
    {/if}

    {if $display_ups_trademarks and $current_carrier eq "UPS"}
        <br />
        {include file="modules/UPS_OnLine_Tools/ups_notice.tpl"}
    {/if}
{/capture}
{include file="dialog.tpl" title=$lng.lbl_aom_edit_totals_title|substitute:"orderid":$order.orderid content=$smarty.capture.dialog extra='width="100%"'}
