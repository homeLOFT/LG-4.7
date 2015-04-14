{*
c0b7a742f56d4cd9d5875fb9a121be48c4674208, v3 (xcart_4_7_0), 2014-12-20 17:15:41, product.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}

{if $product.disabled_product}
  <h2 style="text-align: center;">
    {$lng.lbl_fb_product_disabled_msg}
  </h2>
  <div>
    {$product.disabled_product_message}
  </div>
{else}

  <script type="text/javascript">
    //<![CDATA[
      var productid = '{$product.productid}';
      var product_thumbnail = false;
    //]]>
  </script>

  {include file="form_validation_js.tpl"}

  <h1 class="gray-title product-title"><span>{$product.product}</span></h1>

  <div class="product-details">

    <div class="image{if $max_image_width gte 250} nofloat{/if}"{if $max_image_width lt 250} style="width: {$max_image_width+6}px;"{/if}>

      <div class="image-box">
        <img src="{$ImagesDir}/spacer.gif" class="leveler" style="height: {$max_image_height+6}px;" alt=""/>
        <img src="{$product.image_url}" width="{$product.image_x}" height="{$product.image_y}" id="product_thumbnail" alt="{$product.product|escape}" />
      </div>

      {if $active_modules.Detailed_Product_Images && $product.detailed_images|@is_array}
        <div class="more-images-link">
          <a href="javascript: void(0);" class="more-images-link" onclick="fb_dialog('{$lng.lbl_detailed_images}', $('#detailed-images').html());">{$lng.lbl_fb_more_images}</a> ({$product.detailed_images|@count})
          <div id="detailed-images" style="display: none;">
            <div class="det-images-wrapper">
              <ul class="det-images-content">
                {foreach from=$product.detailed_images item=p name="det_images"}
                  <li class="{if $smarty.foreach.det_images.first}active{/if}">
                    {if $product.detailed_images|@count gt 1}
                      <div class="top-crumbs">
                        {section loop=$product.detailed_images|@count name="bullets_loop"}
                          <span{if $smarty.foreach.det_images.iteration eq $smarty.section.bullets_loop.iteration} class="current"{/if}>&bull;</span>
                        {/section}
                      </div>
                    {/if}
                    <img src="{$ImagesDir}/spacer.gif" class="leveler" alt=""/>
                    <a href="{$p.image_url|default:$p.tmbn_url}" target="_blank"><img src="{$p.image_url|default:$p.tmbn_url}" title="{$lng.lbl_fb_click_to_enlarge}" alt="" /></a>
                  </li>
                {/foreach}
              </ul>
              {if $product.detailed_images|@count gt 1}
                <span class="arrows left" onclick="switch_slide('prev', this);"></span>
                <span class="arrows right" onclick="switch_slide('next', this);"></span>
              {/if}
            </div>
          </div>
        </div>
      {/if}

      <div class="buttons-row" style="width: {$max_image_width}px;">
        <fb:like href="{$product.full_url}" send="true" layout="button_count" width="{$max_image_width}" show_faces="false"></fb:like>
      </div>

    </div>

    <div class="details{if $max_image_width gte 250} nofloat{/if}"{if $max_image_width lt 250} style="margin-left: {$max_image_width}px;"{/if}>

      <form name="orderform" method="post" action="index.php" onsubmit="javascript: return FormValidation(this) && add_to_cart(this);">

        <input type="hidden" name="cart_mode" value="add" />
        <input type="hidden" name="{$data.session[0]}" value="{$data.session[1]}" />
        <input type="hidden" name="productid" value="{$product.productid}" />
        <input type="hidden" name="cat" value="{$cat}" />

        {if ($config.Appearance.show_in_stock eq "Y" && $config.General.unlimited_products ne "Y" && $product.distribution eq "") && $product.product_type neq "C"}
          <div class="property-name product-quantity-row">
            {strip}
              <span class="product-quantity-text" id="product_avail_txt">
                {if $product.avail gt 0}
                  {$lng.txt_fb_items_available|substitute:'items':$product.avail}
                {else}
                  {$lng.lbl_fb_no_items_available}
                {/if}
              </span>
            {/strip}
          </div>
        {/if}

        <div class="property-name sku">
          {$lng.lbl_fb_product_code}:&nbsp;<span id="product_code">{$product.productcode|escape}</span>
        </div>

        {if $product.product_type neq "C"}

          {if $product.weight ne "0.00" || $variants ne ''}
            <div class="property-name" id="product_weight_box"{if $product.weight eq '0.00'} style="display: none;"{/if}>
              {$lng.lbl_weight}:&nbsp;<span id="product_weight">{$product.weight|formatprice}</span>&nbsp;{$config.General.weight_symbol}
            </div>
          {/if}

          {if ($active_modules.Extra_Fields && $extra_fields) || ($active_modules.Feature_Comparison && $product.features.options) || ($active_modules.Subscriptions && $subscription)}
            <table cellspacing="0" class="product-properties" summary="{$lng.lbl_description|escape}">

              {if $active_modules.Extra_Fields}
                {include file="modules/Extra_Fields/product.tpl"}
              {/if}

              {if $active_modules.Feature_Comparison}
                {include file="modules/Feature_Comparison/product.tpl"}
              {/if}

              {if $active_modules.Subscriptions && $subscription}
                {include file="modules/Subscriptions/subscription_info.tpl"}
              {/if}

            </table>

          {/if}

          {if $active_modules.Special_Offers && $config.version gte 4.4}
            <div class="bonus-points-icon">
              {include file='modules/Special_Offers/customer/product_bp_icon.tpl'}
            </div>
          {/if}

        {/if} {* eof: $product.product_type neq "C" *}

        {if !$active_modules.Subscriptions || !$subscription}

          <div class="product-property product-price">

            {strip}

              {if $product.taxed_price ne 0 || $variant_price_no_empty}

                <div class="prices-row">

                  <span class="product-price-value">{include file="`$customer_dir`/currency.tpl" value=$product.taxed_price tag_id="product_price"}</span>
                  {if $config.General.alter_currency_symbol ne ""}&nbsp;
                    <span class="product-alt-price">{include file="`$customer_dir`/alter_currency_value.tpl" alter_currency_value=$product.taxed_price tag_id="product_alt_price"}</span>
                  {/if}
                  {if $product.taxes}
                    <div class="taxes">{include file="customer/main/taxed_price.tpl" taxes=$product.taxes}</div>
                  {/if}

                </div>

              {elseif $product.product_type neq "C"}

                {$lng.lbl_fb_price} <input type="text" size="7" name="price" />

              {/if}

            {/strip}


            {if $product.appearance.has_market_price && $product.appearance.market_price_discount gt 0}
              {strip}
                <div class="product-property market-price">
                  <span class="market-price-value">{include file="`$customer_dir`/currency.tpl" value=$product.list_price}</span>
                  {if $product.appearance.has_market_price && $product.appearance.market_price_discount gt 0},&nbsp;
                    <span class="save">{$lng.lbl_save_price}&nbsp;
                      <span id="save_percent_box">
                        <span id="save_percent">{$product.appearance.market_price_discount}</span>%
                      </span>
                    </span>
                  {/if}
                </div>
              {/strip}
            {/if}

            {if $product.forsale ne "B"}
              <div class="product-property wl-prices">
                {include file="customer/main/product_prices.tpl"}
              </div>
            {/if}

          </div>

        {/if}

        {if $product.product_type ne "C" && ($product.forsale neq "B" || ($product.forsale eq "B" && $smarty.get.pconf ne "" && $active_modules.Product_Configurator))}

          {if $active_modules.Product_Options ne ""}

            <table class="product-options">

              {include file="modules/Product_Options/customer_options.tpl" disable=$lock_options}

            </table>

            <script type="text/javascript">
              //<![CDATA[
              var lbl_no_items_available = "{$lng.lbl_fb_no_items_available|escape:javascript}";
              var txt_items_available = "{$lng.txt_fb_items_available|escape:javascript}";
              $(function(){ldelim}
              $('table.product-options tr:odd').addClass('odd');
              {rdelim});
                //]]>
            </script>

          {/if}


          <table cellspacing="0">

            <tr class="quantity-row">

              {if $product.appearance.empty_stock && ($variants eq '' || ($variants ne '' && $product.avail le 0))}
                <td>
                  <script type="text/javascript">
                    //<![CDATA[
                    var min_avail = 1;
                    var avail = 0;
                    var product_avail = 0;
                    //]]>
                  </script>

                  <span>{$lng.txt_out_of_stock}</span>
                </td>
              {elseif not $product.appearance.force_1_amount && $product.forsale ne "B"}
                <td>
                  {$lng.lbl_fb_quantity}:&nbsp;
                </td>
                <td>
                  <script type="text/javascript">
                    //<![CDATA[
                    var min_avail = {$product.appearance.min_quantity|default:1};
                    var avail = {$product.appearance.max_quantity|default:1};
                    var product_avail = {$product.avail|default:"0"};
                    //]]>
                  </script>

                  <select id="product_avail" name="amount"{if $active_modules.Product_Options ne '' && ($product_options ne '' || $product_wholesale ne '')} onchange="javascript: check_wholesale(this.value);"{/if}>
                    <option value="{$product.appearance.min_quantity}"{if $smarty.get.quantity eq $product.appearance.min_quantity} selected="selected"{/if}>{$product.appearance.min_quantity}</option>
                    {section name=quantity loop=$product.appearance.loop_quantity start=$product.appearance.min_quantity}
                      {if $smarty.section.quantity.index ne $product.appearance.min_quantity}
                        <option value="{$smarty.section.quantity.index}"{if $smarty.get.quantity eq $smarty.section.quantity.index} selected="selected"{/if}>{$smarty.section.quantity.index}</option>
                      {/if}
                    {/section}
                  </select>
                </td>
              {else}
                <td>
                  {$lng.lbl_fb_quantity}:&nbsp;
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
                </td>
              {/if}

              <td>
                <div class="buttons-row buttons-auto-separator">

                  {if $product.appearance.buy_now_buttons_enabled}
                    <button class="button main-button" title="{$lng.lbl_add_to_cart}" type="submit"><span><i>{$lng.lbl_add_to_cart}</i></span></button>
                  {/if}

                  {*ADDTOWL_BUTTON_HERE*}

                </div>
              </td>

            </tr>

          </table>


          {if $product.min_amount gt 1}
            <div class="property-value product-min-amount"><span>{$lng.txt_need_min_amount|substitute:"items":$product.min_amount}</span></div>
          {/if}

        {/if}


        {if $product.appearance.buy_now_buttons_enabled && $product.product_type ne "C" && $product.forsale eq "B"}

          {$lng.txt_pconf_product_is_bundled}

        {elseif $product.product_type eq "C"}

          <div class="button main-button" title="{$lng.lbl_fb_configure_at_online_store}" onclick="javascript: top.location.href='{$current_location}/pconf.php?productid={$product.productid}&_c={$fb_sess}'"><span><i>{$lng.lbl_fb_configure_at_online_store}</i></span></div>

        {/if}

      </form>
      {if $active_modules.Product_Options && ($product_options ne '' || $product_wholesale ne '') && ($product.product_type ne "C" || not $active_modules.Product_Configurator)}
        <script type="text/javascript">
          //<![CDATA[
          if (window.check_options) {ldelim}
          setTimeout(check_options, 200);
          {rdelim}

            setTimeout(resize_window, 200);
            //]]>
        </script>
      {/if}

      {* Feature comparison commented
      {if $active_modules.Feature_Comparison ne ""}
      {include file="modules/Feature_Comparison/product_buttons.tpl"}
      {/if}
      *}

    </div>
    <div class="clearing"></div>

    <dl class="tabs">
      <dt class="selected">{$lng.lbl_fb_description}</dt>
      <dd class="selected">
        <div class="tab-content">
          {$product.fulldescr|default:$product.descr}
        </div>
      </dd>
      <dt>{$lng.lbl_fb_comments}</dt>
      <dd>
        <div class="tab-content">

          <fb:comments href="{$product.full_url}" num_posts="5" width="496"></fb:comments>

        </div>
      </dd>
      <dt>{$lng.lbl_fb_recommendations}</dt>
      <dd>
        <div class="tab-content">

          <div style="overflow: hidden; height: 125px;">
            <fb:recommendations site="{$current_location}" width="496" height="150" header="false" border_color="#fff"></fb:recommendations>
          </div>

        </div>
      </dd>
    </dl>

    {literal}
      <script type="text/javascript">
        //<![CDATA[
        $(function(){
          $('dl.tabs dt').click(function(){
            $(this)
              .siblings().removeClass('selected').end()
              .next('dd').addBack().addClass('selected');

              resize_window();

            });
          });
        //]]>
      </script>
    {/literal}

  </div>


  <script type="text/javascript">
    //<![CDATA[
    {if $active_modules.Product_Options && ($product_options ne '' || $product_wholesale ne '') && ($product.product_type ne "C" || not $active_modules.Product_Configurator)}

      if (window.check_options) {ldelim}
      check_options();
      {rdelim}

        resize_window();
    {/if}

      FB.XFBML.parse();
      //]]>
  </script>


{/if}
