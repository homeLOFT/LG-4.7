{*
d1f618d07bfb3ccbea7c1b9571ffe6befc1e9ff0, v2 (xcart_4_7_2), 2015-04-08 12:02:21, product_details.tpl, mixon

vim: set ts=2 sw=2 sts=2 et:
*}
<form name="orderform" method="post" action="cart.php" onsubmit="javascript: return FormValidation(this);" id="orderform">
  <input type="hidden" name="mode" value="{if $active_modules.Gift_Registry and $wishlistid}wl2cart{else}add{/if}" />
  <input type="hidden" name="productid" value="{$product.productid}" />
  <input type="hidden" name="cat" value="{$smarty.get.cat|escape:"html"}" />
  <input type="hidden" name="page" value="{$smarty.get.page|escape:"html"}" />
  {if $active_modules.Gift_Registry and $wishlistid}
    <input type="hidden" name="fwlitem" value="{$wishlistid}" />
    <input type="hidden" name="eventid" value="{$eventid}" />
  {/if}

  {if $active_modules.Product_Notifications ne ''}
    {if $config.Product_Notifications.prod_notif_enabled_B eq 'Y' and $config.Product_Notifications.prod_notif_show_in_list_B eq 'Y'}
      {assign var="show_notif_B" value="N"}
    {/if}
    {if $config.Product_Notifications.prod_notif_enabled_L eq 'Y' and $config.Product_Notifications.prod_notif_show_in_list_L eq 'Y'}
      {assign var="show_notif_L" value="N"}
    {/if}
  {/if}

  {if $active_modules.Advanced_Customer_Reviews}
    {include file="modules/Advanced_Customer_Reviews/acr_product_details.tpl" break_line="Y"}
  {/if}

  {if $active_modules.Special_Offers}
    {include file="modules/Special_Offers/customer/product_bp_icon.tpl"}
  {/if}

  <table cellspacing="0" class="product-properties" summary="{$lng.lbl_description|escape}">
  <tbody>

    {if $config.Appearance.show_in_stock eq "Y" and $config.General.unlimited_products ne "Y" and $product.distribution eq ""}
      <tr>
        <td class="property-name">{$lng.lbl_in_stock}</td>
        <td class="property-value product-quantity-text" colspan="2">
          {if $product.avail gt 0}
            {$lng.txt_items_available|substitute:"items":$product.avail}
          {else}
            {$lng.lbl_no_items_available}
          {/if}
        </td>
      </tr>
    {/if}

    {if $product.weight ne "0.00" or $variants ne ''}
      <tr id="product_weight_box"{if $product.weight eq '0.00'} style="display: none;"{/if}>
        <td class="property-name">{$lng.lbl_weight}</td>
        <td class="property-value" colspan="2">
          <span id="product_weight">{$product.weight|formatprice}</span> {$config.General.weight_symbol}
        </td>
      </tr>
    {/if}

    {if $active_modules.Extra_Fields}
      {include file="modules/Extra_Fields/product.tpl"}
    {/if}

    {if $active_modules.Feature_Comparison}
      {include file="modules/Feature_Comparison/product.tpl"}
    {/if}

    {if $active_modules.Refine_Filters}
      {include file="modules/Refine_Filters/rf_product.tpl"}
    {/if}

    {if $product.appearance.has_market_price and $product.appearance.market_price_discount gt 0}
      <tr>
        <td class="property-name product-taxed-price">{$lng.lbl_market_price}:</td>
        <td class="property-value product-taxed-price" colspan="2">{currency value=$product.list_price}

          <div class="labels">
            <div class="label market-price" id="save_percent_box">
              {$lng.lbl_save_price} <span id="save_percent">{$product.appearance.market_price_discount}</span>%
            </div>
          </div>

        </td>
      </tr>
    {/if}

    {if $active_modules.XPayments_Subscriptions and $product.subscription.subscription_product eq 'Y'}
      {include file="modules/XPayments_Subscriptions/customer/product_details.tpl"}
    {else}
    <tr>
      <td class="property-name product-price" valign="top">{$lng.lbl_our_price}:</td>
      <td class="property-value" valign="top" colspan="2">
      {if $product.taxed_price ne 0 or $variant_price_no_empty}
        <span class="product-price-value">{currency value=$product.taxed_price tag_id="product_price"}</span>
        {if $product.taxes}
          <br />{include file="customer/main/taxed_price.tpl" taxes=$product.taxes}
        {/if}

        {if $active_modules.Klarna_Payments}
          {include file="modules/Klarna_Payments/monthly_cost.tpl" elementid="pp_conditions`$product.productid`" monthly_cost=$product.monthly_cost}
        {/if}

      {else}
        <input type="text" size="7" name="price" />
      {/if}
      </td>
    </tr>
    {$lng.lbl_pdp_promo_text}
    {/if}
      
    {if $active_modules.Product_Notifications ne '' and $config.Product_Notifications.prod_notif_enabled_P eq 'Y' and ($product.taxed_price ne 0 or $variant_price_no_empty)}
    <tr>
      <td colspan="3">
          {include file="modules/Product_Notifications/product_notification_request_button.tpl" productid=$product.productid type="P"}
      </td>
    </tr>
    {/if}
    {if $product.forsale ne "B"}
      <tr>
        <td colspan="3">{include file="customer/main/product_prices.tpl"}</td>
      </tr>
    {/if}

    {if $active_modules.Product_Notifications ne '' and $config.Product_Notifications.prod_notif_enabled_P eq 'Y'}
      <tr>
        <td class="property-value" valign="top" colspan="3">
          {include file="modules/Product_Notifications/product_notification_request_form.tpl" productid=$product.productid type="P"}
        </td>
      </tr>
    {/if}


    {if $product.forsale neq "B" or ($product.forsale eq "B" and $smarty.get.pconf ne "" and $active_modules.Product_Configurator)}

      {if $active_modules.Product_Options ne ""}
        {if $product_options ne ''}
          </tbody>
          <tbody class="product-options">
        {/if}
        {include file="modules/Product_Options/customer_options.tpl" disable=$lock_options}
      {/if}

    {/if}

  </tbody>
  </table><!--/product-properties-->

  {if $product.forsale neq "B" or ($product.forsale eq "B" and $smarty.get.pconf ne "" and $active_modules.Product_Configurator)}
    <div class="quantity-row">

        {if $product.appearance.empty_stock and ($variants eq '' or ($variants ne '' and $product.avail le 0))}

          <div class="quantity">
            {$lng.lbl_qty}
<script type="text/javascript">
//<![CDATA[
var min_avail = 1;
var avail = 0;
var product_avail = 0;
//]]>
</script>

            <strong>{$lng.txt_out_of_stock}</strong>

            {if $active_modules.Product_Notifications ne ''}
              {if $config.Product_Notifications.prod_notif_enabled_L eq 'Y' and $product_options ne ''}
                {assign var="show_notif_L" value="Y"}
              {/if}
              {if $config.Product_Notifications.prod_notif_enabled_B eq 'Y'}
                {assign var="show_notif_B" value="Y"}
              {/if}
            {/if}

          </div>
          
        {elseif not $product.appearance.force_1_amount and $product.forsale ne "B"}

          <div class="quantity">
            {if $config.Appearance.show_in_stock eq "Y" and not $product.appearance.quantity_input_box_enabled and $config.General.unlimited_products ne 'Y'}
              {$lng.lbl_quantity_x|substitute:quantity:$product.avail}
            {else}
              {$lng.lbl_qty}
            {/if}
  
<script type="text/javascript">
//<![CDATA[
var min_avail = {$product.appearance.min_quantity|default:1};
var avail = {$product.appearance.max_quantity|default:1};
var product_avail = {$product.avail|default:"0"};
//]]>
</script>
            <input type="text" id="product_avail_input" name="amount" maxlength="11" size="1" onchange="javascript: return check_quantity_input_box(this);" value="{$smarty.get.quantity|escape:"html"|default:$product.appearance.min_quantity}"{if not $product.appearance.quantity_input_box_enabled} disabled="disabled" style="display: none;"{/if}/>
            {if $product.appearance.quantity_input_box_enabled and $config.Appearance.show_in_stock eq "Y" and $config.General.unlimited_products ne 'Y'}
              <span id="product_avail_text" class="quantity-text">{$lng.lbl_product_quantity_from_to|substitute:"min":$product.appearance.min_quantity:"max":$product.avail}</span>
            {/if}

            <select id="product_avail" name="amount"{if $active_modules.Product_Options ne '' and ($product_options ne '' or $product_wholesale ne '')} onchange="javascript: check_wholesale(this.value);"{/if}{if $product.appearance.quantity_input_box_enabled} disabled="disabled" style="display: none;"{/if}>
                <option value="{$product.appearance.min_quantity}"{if $smarty.get.quantity eq $product.appearance.min_quantity} selected="selected"{/if}>{$product.appearance.min_quantity}</option>
              {if not $product.appearance.quantity_input_box_enabled}
                {section name=quantity loop=$product.appearance.loop_quantity start=$product.appearance.min_quantity}
                  {if $smarty.section.quantity.index ne $product.appearance.min_quantity}
                    <option value="{$smarty.section.quantity.index}"{if $smarty.get.quantity eq $smarty.section.quantity.index} selected="selected"{/if}>{$smarty.section.quantity.index}</option>
                  {/if}
                {/section}
              {/if}
            </select>

            {if $active_modules.Product_Notifications ne ''}
              {if $config.Product_Notifications.prod_notif_enabled_L eq 'Y' and ($product.avail gt $config.Product_Notifications.prod_notif_L_amount or $product_options ne '')}
                {assign var="show_notif_L" value="Y"}
              {/if}
              {if $config.Product_Notifications.prod_notif_enabled_B eq 'Y' and $product_options ne ''}
                {assign var="show_notif_B" value="Y"}
              {/if}
            {/if}

          </div>

        {else}

          <div class="quantity">
            {$lng.lbl_qty}

<script type="text/javascript">
//<![CDATA[
var min_avail = 1;
var avail = 1;
var product_avail = 1;
//]]>
</script>

            <span class="product-one-quantity">1</span>
            <input type="hidden" name="amount" value="1" />

            {if $product.distribution ne ""}
              {$lng.txt_product_downloadable}
            {/if}
          </div>

        {/if}

        {if $product.appearance.buy_now_buttons_enabled}
          {if $product.forsale ne "B" and not ($smarty.get.pconf ne "" and $active_modules.Product_Configurator)}
            <div class="buttons-row">

              {* Uncomment this line if you don't want buy more button behavior:
                {include file="customer/buttons/buy_now.tpl" type="input" additional_button_class="main-button"}
              *}
              {* Comment the following 5 lines if you don't want buy more button behavior: *}
              {if $product.appearance.added_to_cart}
                {include file="customer/buttons/buy_more.tpl" type="input" additional_button_class="main-button"}
              {else}
                {include file="customer/buttons/buy_now.tpl" type="input" additional_button_class="main-button"}
              {/if}

              {if $product.appearance.dropout_actions}
                {include file="customer/buttons/add_to_list.tpl" id=$product.productid js_if_condition="FormValidation()"}

              {elseif $product.appearance.buy_now_add2wl_enabled}
                {include file="customer/buttons/add_to_wishlist.tpl" href="javascript: if (FormValidation()) submitForm(document.orderform, 'add2wl', arguments[0]);"}
              {/if}

            </div>
          {elseif $product.forsale eq "B"}

            {$lng.txt_pconf_product_is_bundled}

          {/if}
        {/if}

        {if $product.min_amount gt 1}
          <div class="product-min-amount">{$lng.txt_need_min_amount|substitute:"items":$product.min_amount}</div>
        {/if}

      <div class="clearing"></div>
    </div><!--/quantity-row-->
  {/if}
    
  {if $show_notif_L eq 'Y'}
    {include file="modules/Product_Notifications/product_notification_request_button.tpl" productid=$product.productid type="L"}
  {/if}
  {if $show_notif_B eq 'Y'}
    {include file="modules/Product_Notifications/product_notification_request_button.tpl" productid=$product.productid type="B"}
  {/if}
  {if $active_modules.Product_Notifications ne ''}
    {if $config.Product_Notifications.prod_notif_enabled_B eq 'Y'}
      {include file="modules/Product_Notifications/product_notification_request_form.tpl" productid=$product.productid variantid=$product.variantid|default:0 type="B"}
    {/if}

    {if $config.Product_Notifications.prod_notif_enabled_L eq 'Y'}
      {include file="modules/Product_Notifications/product_notification_request_form.tpl" productid=$product.productid variantid=$product.variantid|default:0 type="L"}
    {/if}
  {/if}

  {if $active_modules.Bill_Me_Later and $config.Bill_Me_Later.bml_enable_banners eq 'Y' and $config.Bill_Me_Later.bml_banner_on_product eq 'inline'}
    {include file="modules/Bill_Me_Later/banner.tpl" bml_page='product'}
  {/if}

  {if $product.appearance.buy_now_buttons_enabled}


    {if $smarty.get.pconf ne "" and $active_modules.Product_Configurator}

      <input type="hidden" name="slot" value="{$smarty.get.slot|escape:"html"}" />
      <input type="hidden" name="addproductid" value="{$product.productid}" />

      <div class="button-row">
        {include file="customer/buttons/button.tpl" button_title=$lng.lbl_pconf_add_to_configuration href="javascript: if (FormValidation()) `$ldelim`document.orderform.productid.value='`$smarty.get.pconf`'; document.orderform.action='pconf.php'; document.orderform.submit();`$rdelim`" additional_button_class="light-button"}
      </div>

      {if $product.appearance.empty_stock}
        <p class="message">
          <strong>{$lng.lbl_note}:</strong> {$lng.lbl_pconf_slot_out_of_stock_note}
        </p>
      {/if}

      {if $product.appearance.min_quantity eq $product.appearance.max_quantity}
        <p>{$lng.txt_add_to_configuration_note|substitute:"items":$product.appearance.min_quantity}</p>
      {/if}

    {/if}

  {/if}

</form>

<div class="clearing"></div>

{if $active_modules.Product_Options and ($product_options ne '' or $product_wholesale ne '') and ($product.product_type ne "C" or not $active_modules.Product_Configurator)}
<script type="text/javascript">
//<![CDATA[
setTimeout(check_options, 200);
//]]>
</script>
{/if}

{if $active_modules.Feature_Comparison ne ""} 
  {include file="modules/Feature_Comparison/product_buttons.tpl"}
{/if}

{if $product_details_standalone}
{load_defer_code type="css"}
{load_defer_code type="js"}
{/if}
