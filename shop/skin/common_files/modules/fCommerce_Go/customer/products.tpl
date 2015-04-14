{* cbbf01fde6ad5261122f17ed13914a23bd36a2ef, v2 (xcart_4_6_5), 2014-10-18 12:46:37, products.tpl, aim *}
{if $products}

  {if $title}
    <h2 class="gray-title{if $cat} category-page{/if}"><span>{$title}</span></h2>
  {/if}

  {if $navigation}
    <div class="nav-bar-wrapper">
      <div class="nav-bar">
        <div class="list-view-selector">
          <span title="{$lng.lbl_fb_grid}" class="matrix-style active" onclick="change_list_view('matrix-style', this);"></span>
          <span title="{$lng.lbl_fb_list}" class="list-style" onclick="change_list_view('list-style', this);"></span>
          <span title="{$lng.lbl_fb_table}" class="table-style" onclick="change_list_view('table-style', this);"></span>
        </div>
        <div class="per-page">
          {$lng.lbl_fb_per_page} <input type="text" size="1" value="{$data.objects_per_page}" />
          <a href="javascript: void(0);" onclick="ajax_process('cat={$cat}&fb_mode={$data.mode}&sort={$data.sort}&page={$data.shop_configuration.current_page}&per_page={$data.objects_per_page}&substring={$data.substring}&sort_direction={$data.sort_direction}&per_page={$data.total_items}');">{$lng.lbl_fb_show_all}</a>
          <script type="text/javascript">
             //<![CDATA[
            $(function(){ldelim}
            $("div.per-page input").change(function(){ldelim}

            ajax_process("cat={$cat}&fb_mode={$data.mode}&sort={$data.sort}&page={$data.shop_configuration.current_page}&per_page={$data.objects_per_page}&substring={$data.substring}&sort_direction={$data.sort_direction}&per_page="+$(this).val());

            {rdelim});
            {rdelim});
              //]]>
          </script>

        </div>
        {if $data.sort_fields}
          <div class="sort-fields">
            {*$lng.lbl_sort_by*}
            <select id="sort-by-selector">
              {foreach from=$data.sort_fields item=v key=k}
                <option value="{$k}"{if $k eq $data.sort_line} selected="selected"{/if}>{$v}</option>
              {/foreach}
            </select>

            <script type="text/javascript">
               //<![CDATA[
              $("#sort-by-selector").change(function(){ldelim}
              ajax_process("cat={$cat}&fb_mode={$data.mode}&sort_direction={$data.sort_direction}&per_page={$data.objects_per_page}&substring={$data.substring}&page={$data.current_page}&"+$(this).val());
              {rdelim});
                //]]>
            </script>

          </div>
        {/if}
        <div class="clearing"></div>
      </div>
      <div class="pages">
        {include file="`$customer_dir`/navigation.tpl"}
      </div>
    </div>
  {/if}

  <div class="products matrix-style">
    {foreach from=$products item=p}
      <div class="product-item{cycle values=' highlight,'}" style="width: {$data.prod_tmbn_width+45}px;">
          <div class="product-item-wrapper" onclick="ajax_process('cat={$p.categoryid}&productid={$p.productid}&fb_mode=product_details');">
            <div class="image" style="width: {$p.tmbn_x+5}px;">
              <img src="{$p.tmbn_url}" class="thumbnail" style="width: {if $p.default_image}{$data.prod_tmbn_width}px{else}{$p.tmbn_x}px; height: {$p.tmbn_y}px{/if};" alt="{$p.product|escape}" />
              <img src="tpls/tab/images/spacer.gif" class="leveler" style="height: {$data.prod_tmbn_height|default:1}px;" alt="" />
              {if $p.have_offers}
                <img src="tpls/tab/images/offer_sign.png" class="spec-offer" alt="" />
              {/if}
            </div>
            <div class="details">
              <div class="product-title">
                <a href="javascript: void(0);" class="product-title">
                  {$p.product}
                </a>
                {if $config.Appearance.display_productcode_in_list eq "Y" && $p.productcode}
                  <span class="sku">
                    {$p.productcode}
                  </span>
                {/if}
              </div>

              <div class="info">
                <div class="description">
                  {$p.descr|strip_tags:false}
                </div>

                {if $p.product_type ne "C"}

                  {if $active_modules.Subscriptions ne "" and $p.catalogprice}

                    {include file="modules/Subscriptions/subscription_info_inlist.tpl"}

                  {elseif $p.appearance.is_auction}

                    <span class="price">{$lng.lbl_enter_your_price}</span><br />
                    {$lng.lbl_enter_your_price_note}

                  {else}

                    {if $p.appearance.has_price}

                      <span class="product-price">
                        {include file="`$customer_dir`/currency.tpl" value=$p.taxed_price}
                        {if $config.General.alter_currency_symbol ne ""}
                          <span class="alter-price">{include file="`$customer_dir`/alter_currency_value.tpl" alter_currency_value=$p.taxed_price}</span>
                        {/if}

                        {if $p.appearance.has_market_price and $p.appearance.market_price_discount gt 0}
                          {strip}
                            <span class="market-price">
                              <span class="market-price-value">{include file="`$customer_dir`/currency.tpl" value=$p.list_price}</span>,&nbsp;
                              {if $p.appearance.market_price_discount gt 0}
                                <span class="price-save">{$lng.lbl_save_price} {$p.appearance.market_price_discount}%</span>
                              {/if}

                            </span>
                          {/strip}
                        {/if}
                        {if $p.taxes}
                          <span class="taxes">{include file="customer/main/taxed_price.tpl" taxes=$p.taxes is_subtax=true}</span>
                        {/if}
                      </span>

                    {/if}

                    {if $active_modules.Special_Offers and $p.use_special_price}
                      {include file="modules/Special_Offers/customer/product_special_price.tpl"}
                    {/if}

                  {/if}

                {/if}
              </div>
            </div>
          </div>
          <div class="list-separate"></div>
      </div>
    {/foreach}
  <div class="clearing"></div>
</div>

{if $navigation}
  <div class="pages">
    {include file="`$customer_dir`/navigation.tpl"}
  </div>
  <div class="clearing"></div>
{/if}

{if $cat}
  <script type="text/javascript">
     //<![CDATA[
    $(function(){ldelim}
    change_list_view(list_view, $(".list-view-selector span."+list_view).get(0));
    {rdelim});
     //]]>
  </script>
{/if}

{else}
  {if $data.mode eq 'search'}
    <h3>
      {$lng.txt_no_products_found}
    </h3>
  {/if}
{/if}

<script type="text/javascript">
  var productid = false;
</script>
