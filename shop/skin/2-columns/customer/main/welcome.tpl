{*
4df8a686b03b90736f3e9226848cfbe174d4cf27, v6 (xcart_4_6_2), 2014-01-07 10:38:51, welcome.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}

{include file="customer/news.tpl"}

{if $display_greet_visitor_name}

  <h1>{$lng.lbl_welcome_back|substitute:"name":$display_greet_visitor_name} </h1>

{elseif $lng.lbl_site_title}

  <h1>{$lng.lbl_welcome_to|substitute:"company":$lng.lbl_site_title|amp}</h1>

{else}

  <h1>{$lng.lbl_welcome_to|substitute:"company":$config.Company.company_name|amp}</h1>

{/if}

{include file="customer/main/home_page_banner.tpl"}

{$lng.txt_welcome}<br />

<div class="clearing"></div>

{if $active_modules.Bestsellers and $config.Bestsellers.bestsellers_menu ne "Y"}
  {include file="modules/Bestsellers/bestsellers.tpl"}<br />
{/if}

{if $active_modules.New_Arrivals}
  {include file="modules/New_Arrivals/new_arrivals.tpl" is_home_page="Y"}
{/if}

{if $active_modules.Refine_Filters}
  {include file="modules/Refine_Filters/home_products.tpl"}
{/if}

{if $active_modules.On_Sale}
  {include file="modules/On_Sale/on_sale.tpl" is_home_page="Y"}
{/if}

{include file="customer/main/featured.tpl"}
