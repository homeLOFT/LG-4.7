{*
3fb62b7b4fb831f99c2dff2bb4cd5ad8088c7ec7, v6 (xcart_4_6_2), 2014-01-04 06:49:25, welcome.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
<div class="welcome-table">

  {if $active_modules.Bestsellers}
    {getvar var=bestsellers func=func_tpl_get_bestsellers}
  {/if}

	<div class="welcome-cell{if $active_modules.Bestsellers and $bestsellers and $config.Bestsellers.bestsellers_menu eq 'Y'} with-bestsellers{/if}">
	
    {include file="customer/main/home_page_banner.tpl"}
 		{$lng.txt_welcome}

		{if $active_modules.Bestsellers and $config.Bestsellers.bestsellers_menu ne "Y"}
		  {include file="modules/Bestsellers/bestsellers.tpl"}<br />
		{/if}
		{if $active_modules.Bestsellers && $bestsellers}
			{assign var=row_length value=2}
		{else}
			{assign var=row_length value=false}
		{/if}

        <script type="text/javascript">
          {literal}
          //<![CDATA[
          $(function() {
            if (isLocalStorageSupported()) {
              var _storage_key = 'welcome-tabs'+xcart_web_dir;
              // Take into account EU cookie law
              var _used_storage = ('function' != typeof window.func_is_allowed_cookie || func_is_allowed_cookie('welcome-tabs')) ? localStorage : sessionStorage;
              var myOpts = {
                active   : parseInt(_used_storage[_storage_key]) || 0,
                activate : function( event, ui ){
                    _used_storage[_storage_key] = ui.newTab.index();
                }
              };
            } else {
              var myOpts = {}
            }

            $('#welcome-tabs-container').tabs(myOpts);
          });
          //]]>
          {/literal}
        </script>	
        <div id="welcome-tabs-container">
          <ul>
            {if $active_modules.New_Arrivals and $new_arrivals and $config.New_Arrivals.new_arrivals_home eq 'Y'}
              <li><a href="#new-arrivals">{$lng.lbl_new_arrivals}</a></li>
            {/if}
            {if $active_modules.On_Sale and $on_sale_products and $config.On_Sale.on_sale_home eq 'Y'}
              <li><a href="#on-sale">{$lng.lbl_on_sale}</a></li>
            {/if}
            {if $f_products}
              <li><a href="#featured-products">{$lng.lbl_featured_products}</a></li>
            {/if}
          </ul>
          {if $active_modules.New_Arrivals and $new_arrivals and $config.New_Arrivals.new_arrivals_home eq 'Y'}
            <div id="new-arrivals">
              {include file="modules/New_Arrivals/new_arrivals.tpl" is_home_page="Y" noborder="true"}
            </div>
          {/if}
          {if $active_modules.On_Sale and $on_sale_products and $config.On_Sale.on_sale_home eq 'Y'}
            <div id="on-sale">
              {include file="modules/On_Sale/on_sale.tpl" is_home_page="Y" noborder="true"}
            </div>
          {/if}
          {if $f_products}
            <div id="featured-products">
              {include file="customer/main/featured.tpl" row_length=$row_length noborder="true"}
            </div>
          {/if}
          </div>
	</div>

	{if $active_modules.Bestsellers && $bestsellers}
	<div class="bestsellers-cell">
		{include file="modules/Bestsellers/menu_bestsellers.tpl"}
	</div>
	{/if}
</div>
<div class="clearing"></div>
