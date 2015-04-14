{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, search_result.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{if $mode ne "search" or $products eq ""}

  <h1>{$lng.lbl_advanced_search}</h1>

  <script type="text/javascript" src="{$SkinDir}/js/reset.js"></script>

<script type="text/javascript">
//<![CDATA[
var searchform_def = [
  ['posted_data[substring]', ''],
  ['posted_data[including]', 'all'],
  ['posted_data[search_in_subcategories]', true],
  ['posted_data[by_title]', true],
  ['posted_data[by_descr]', true],
  ['posted_data[by_keywords]', true],
  ['posted_data[by_sku]', true],
  ['posted_data[price_min]', '{$search_prefilled_default.price_min}'],
  ['posted_data[price_max]', '{$search_prefilled_default.price_max}'],
  ['posted_data[avail_min]', '0'],
  ['posted_data[weight_min]', '{$search_prefilled_default.weight_min}'],
  ['posted_data[weight_max]', '{$search_prefilled_default.weight_max}'],
{if $active_modules.Extra_Fields and $extra_fields ne ''}
{foreach from=$extra_fields item=v}
  ['posted_data[extra_fields][{$v.fieldid}]', false],
{/foreach}
{/if}
{if $active_modules.Manufacturers and $manufacturers ne '' and $config.Search_products.search_products_manufacturers eq 'Y'}
  ['posted_data[manufacturers][]', '{$search_prefilled_default.manufacturerids}'],
{/if}
  ['posted_data[categoryid]', '{$search_prefilled_default.categoryid}']
];
//]]>
</script>

  {capture name=dialog}

    <form name="searchform" action="search.php" method="post">
      <input type="hidden" name="mode" value="search" />

      <div class="responsive-search">
            <div class="data-name">{$lng.lbl_search_for_pattern}:</div>
            <div class="data-input pattern"><input type="text" name="posted_data[substring]" value="{$search_prefilled.substring|escape}" autofocus /></div>
          
          
            <div class="search-button">
              {include file="customer/buttons/button.tpl" button_title=$lng.lbl_search type="input" additional_button_class="main-button"}
            </div>
          
            <div class="data-name search-options">&nbsp;</div>
            
            <div class="input-row">

              <label>
                <input type="radio" name="posted_data[including]" value="all"{if $is_empty_search_prefilled or $search_prefilled.including eq '' or $search_prefilled.including eq 'all'} checked="checked"{/if} />
                {$lng.lbl_all_word}
              </label>

              <label>
                <input type="radio" name="posted_data[including]" value="any"{if $search_prefilled.including eq 'any'} checked="checked"{/if} />
                {$lng.lbl_any_word}
              </label>

              <label>
                <input type="radio" name="posted_data[including]" value="phrase"{if $search_prefilled.including eq 'phrase'} checked="checked"{/if} />
                {$lng.lbl_exact_phrase}
              </label>

            </div>
            

          
            <div class="data-name">{$lng.lbl_search_in}:</div>
          
          
          
            <div class="input-row">

              <label>
                <input type="checkbox" name="posted_data[by_title]"{if $is_empty_search_prefilled or $search_prefilled.by_title} checked="checked"{/if} />
                {$lng.lbl_product_title}
              </label>

              <label>
                <input type="checkbox" id="posted_data_by_descr" name="posted_data[by_descr]"{if $is_empty_search_prefilled or $search_prefilled.by_descr} checked="checked"{/if} />
                {$lng.lbl_description}
              </label>

              <label>
                <input type="checkbox" id="posted_data_by_sku" name="posted_data[by_sku]"{if $is_empty_search_prefilled or $search_prefilled.by_sku} checked="checked"{/if} />
                {$lng.lbl_sku}
              </label>

            </div>
          

          {if $active_modules.Extra_Fields and $extra_fields ne ''}

            
              <div class="data-name">{$lng.lbl_search_also_in}:</div>
              <div class="search-extra-fields input-row">

                {foreach from=$extra_fields item=v}
                  <label>
                    <input type="checkbox" name="posted_data[extra_fields][{$v.fieldid}]"{if $v.selected eq "Y"} checked="checked"{/if} />
                    {$v.field}
                  </label>
                {/foreach}
              </div>
            

          {/if}
        

        {if $config.Search_products.search_products_category eq 'Y' or ($active_modules.Manufacturers and $config.Search_products.search_products_manufacturers eq 'Y') or $config.Search_products.search_products_price eq 'Y' or $config.Search_products.search_products_weight eq 'Y'}


          <div>
            {include file="customer/visiblebox_link.tpl" id="adv_search_box" title=$lng.lbl_advanced_search_options visible=$search_prefilled.need_advanced_options}
          </div>
            
          

          <div id="adv_search_box"{if not $search_prefilled.need_advanced_options} style="display: none;"{/if}>

            {if $config.Search_products.search_products_category eq 'Y'}
              
                <div class="data-name">{$lng.lbl_search_in_category}:</div>
                <div class="input-row">
                  <select name="posted_data[categoryid]" class="adv-search-select">
                    <option value="">&nbsp;</option>
                    {foreach from=$search_categories item=v key=k}
                      <option value="{$k}"{if $search_prefilled.categoryid eq $k} selected="selected"{/if}>{if $config.UA.browser eq "MSIE"}{$v|truncate:60:'...':true:true|amp}{else}{$v|amp}{/if}</option>
                    {/foreach}
                  </select>
                </div>
              

              
                <div class="data-name search-options">&nbsp;</div>
                <div class="input-row">
                  <label>
                    <input type="checkbox" name="posted_data[search_in_subcategories]"{if $is_empty_search_prefilled or $search_prefilled.search_in_subcategories} checked="checked"{/if} />
                    {$lng.lbl_search_in_subcategories}
                  </label>
                </div>
              

            {/if}

            {if $active_modules.Manufacturers and $manufacturers ne '' and $config.Search_products.search_products_manufacturers eq 'Y'}

              {capture name=manufacturers_items} 
                {section name=mnf loop=$manufacturers}
                  <option value="{$manufacturers[mnf].manufacturerid}"{if $manufacturers[mnf].selected eq 'Y'} selected="selected"{/if}>{$manufacturers[mnf].manufacturer}</option>
                {/section}
              {/capture}

              
                <div class="data-name">{$lng.lbl_manufacturers}:</div>
                <div class="input-row">
                  <select name="posted_data[manufacturers][]" multiple="multiple" size="{if $smarty.section.mnf.total gt 5}5{else}{$smarty.section.mnf.total}{/if}">
                    {$smarty.capture.manufacturers_items}
                  </select>
                </div>
              

            {/if}

            {if $config.Search_products.search_products_price eq 'Y'}
              
                <div class="data-name">{$lng.lbl_price} ({$config.General.currency_symbol}):</div>
                <div class="input-row">
                  <input type="text" size="10" maxlength="15" name="posted_data[price_min]" value="{$search_prefilled.price_min|escape}" />
                  &nbsp;-&nbsp;
                  <input type="text" size="10" maxlength="15" name="posted_data[price_max]" value="{$search_prefilled.price_max|escape}" />
                </div>
              
            {/if}

            {if $config.Search_products.search_products_weight eq 'Y'}
              
                <div class="data-name">{$lng.lbl_weight} ({$config.General.weight_symbol}):</div>
                <div class="input-row">
                  <input type="text" size="10" maxlength="10" name="posted_data[weight_min]" value="{$search_prefilled.weight_min|escape}" />
                  &nbsp;-&nbsp;
                  <input type="text" size="10" maxlength="10" name="posted_data[weight_max]" value="{$search_prefilled.weight_max|escape}" />
                </div>
              
            {/if}

            
              <div class="button-row">
                <div class="data-name">
                  {include file="customer/buttons/button.tpl" button_title=$lng.lbl_reset_filter style="link" href="javascript: reset_form('searchform', searchform_def);"}
                </div>
                <div class="search-button">
                  {include file="customer/buttons/button.tpl" button_title=$lng.lbl_search type="input" additional_button_class="main-button"}
                </div>
              </div>
            
          </div>

        {/if}
      </div>

    </form>

  {/capture}
  {include file="customer/dialog.tpl" title=$lng.lbl_search_products content=$smarty.capture.dialog additional_class="adv-search" noborder=true}

{/if}

<a name="results"></a>

{if $mode eq "search"}

  {if $products ne ""}
    <h1>{$lng.lbl_search_results}</h1>
  {/if}

  {if $total_items gt "1"}
    <div class="results-found">
    {if $total_rough_pages}{$lng.txt_N_results_found_rough|substitute:"items":$total_rough_pages}{else}{$lng.txt_N_results_found|substitute:"items":$total_items}{/if}. 
     {$lng.txt_displaying_X_Y_results|substitute:"first_item":$first_item:"last_item":$last_item}
    </div>
    <div class="search-again">
      {include file="customer/buttons/button.tpl" button_title=$lng.lbl_search_again href="search.php" style="link"}
    </div>

  {elseif $total_items eq "0"}
    {$lng.txt_N_results_found|substitute:"items":0}
    <br />
  {/if}

  <br />

{/if}

{if $mode eq "search" and $products ne ""}

  {capture name=dialog}

    {if $total_pages gt 2}
      {assign var="navpage" value=$navigation_page}
    {/if}

    {include file="customer/main/navigation.tpl"}

    {include file="customer/main/products.tpl"}

    {include file="customer/main/navigation.tpl" per_page=""}

    {if $search_url ne ""}
      <div class="right-box this-page-url"><a href="{$search_url|amp}" class="small-link">{$lng.lbl_this_page_url}</a></div>
    {/if}

  {/capture}
  {include file="customer/dialog.tpl" title=$lng.lbl_products content=$smarty.capture.dialog sort=true additional_class="products-dialog"}

{/if}
