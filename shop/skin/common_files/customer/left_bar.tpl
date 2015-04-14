{*
c863034063dde05a16bb4f2a2984f56cd779cc10, v6 (xcart_4_5_5), 2013-01-28 14:29:28, left_bar.tpl, random 
vim: set ts=2 sw=2 sts=2 et:
*}
{include file="customer/categories.tpl"}

{if $active_modules.Refine_Filters}
  {include file="modules/Refine_Filters/customer_filter.tpl"}
{/if}

{if $active_modules.Advanced_Customer_Reviews}
  {include file="modules/Advanced_Customer_Reviews/customer_reviews_menu.tpl"}
{/if}

{if $active_modules.Bestsellers}
  {include file="modules/Bestsellers/menu_bestsellers.tpl"}
{/if}

{if $active_modules.New_Arrivals}
  {include file="modules/New_Arrivals/menu_new_arrivals.tpl"}
{/if}

{if $active_modules.Manufacturers ne "" and $config.Manufacturers.manufacturers_menu eq "Y"}
  {include file="modules/Manufacturers/menu_manufacturers.tpl"}
{/if}

{include file="customer/special.tpl"}

{if $active_modules.Survey and $menu_surveys}
  {foreach from=$menu_surveys item=menu_survey}
    {include file="modules/Survey/menu_survey.tpl"}
  {/foreach}
{/if}

{include file="customer/help/menu.tpl"}

{if $active_modules.Banner_System and $left_banners ne ''}
  {include file="modules/Banner_System/banner_rotator.tpl" banners=$left_banners banner_location='L'}
{/if}
