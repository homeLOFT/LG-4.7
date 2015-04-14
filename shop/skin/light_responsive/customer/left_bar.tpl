{*
44bfc83815ad5e5c06a58f653cbba8c5f1b89b87, v2 (xcart_4_7_0), 2015-03-02 13:29:05, left_bar.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{*include file="customer/menu_cart.tpl"*}

{if $active_modules.Feature_Comparison && $comparison_products ne ''}
  {include file="modules/Feature_Comparison/product_list.tpl" }
{/if}

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

{if $active_modules.Recently_Viewed}
  {include file="modules/Recently_Viewed/section.tpl"}
{/if}

{if $active_modules.Manufacturers ne "" and $config.Manufacturers.manufacturers_menu eq "Y"}
  {include file="modules/Manufacturers/menu_manufacturers.tpl"}
{/if}
{if $active_modules.Gift_Certificates ne ""}
  {include file="modules/Gift_Certificates/gc_menu.tpl"}
{/if}
{include file="customer/special.tpl"}
{if $active_modules.Adv_Mailchimp_Subscription}
    {include file="modules/Adv_Mailchimp_Subscription/customer/mailchimp_news.tpl"}
{else}
    {include file="customer/news.tpl"}
{/if}

{if $active_modules.Survey and $menu_surveys}
  {foreach from=$menu_surveys item=menu_survey}
    {include file="modules/Survey/menu_survey.tpl"}
  {/foreach}
{/if}

{if $active_modules.XAffiliate && $config.XAffiliate.partner_register eq 'Y' && $config.XAffiliate.display_backoffice_link eq 'Y'}
  {include file="partner/menu_affiliate.tpl" }
{/if}

{if $active_modules.Interneka}
  {include file="modules/Interneka/menu_interneka.tpl" }
{/if}

{if not $active_modules.Simple_Mode and $config.General.provider_register eq 'Y' and $config.General.provider_display_backoffice_link eq 'Y'}
  {include file="customer/menu_provider.tpl"}
{/if}

{if $active_modules.Testimonials}
  {include file="modules/Testimonials/customer_menu_testimonials.tpl" }
{/if}

{if $active_modules.Banner_System and $left_banners ne ''}
  {include file="modules/Banner_System/banner_rotator.tpl" banners=$left_banners banner_location='L'}
{/if}

{include file="poweredby.tpl"}
