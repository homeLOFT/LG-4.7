{*
a8db8e3a479caba8063b0536bdee66d8344ecaf9, v7 (xcart_4_6_5), 2014-10-10 16:04:37, right_bar.tpl, aim 
vim: set ts=2 sw=2 sts=2 et:
*}
{if $active_modules.Feature_Comparison and $comparison_products ne ''}
  {include file="modules/Feature_Comparison/product_list.tpl"}
{/if}

{include file="customer/menu_cart.tpl"}

{include file="customer/authbox.tpl"}

{if $active_modules.Recently_Viewed}
  {include file="modules/Recently_Viewed/section.tpl"}
{/if}

{if $active_modules.Adv_Mailchimp_Subscription}
  {include file="modules/Adv_Mailchimp_Subscription/customer/mailchimp_news.tpl"}
{else}
  {include file="customer/news.tpl"}
{/if}

{if $active_modules.XAffiliate and $config.XAffiliate.partner_register eq 'Y' and $config.XAffiliate.display_backoffice_link eq 'Y'}
  {include file="partner/menu_affiliate.tpl"}
{/if}

{if not $active_modules.Simple_Mode and $config.General.provider_register eq 'Y' and $config.General.provider_display_backoffice_link eq 'Y'}
  {include file="customer/menu_provider.tpl"}
{/if}

{if $active_modules.Interneka}
  {include file="modules/Interneka/menu_interneka.tpl"}
{/if}

{if $active_modules.Testimonials}
  {include file="modules/Testimonials/customer_menu_testimonials.tpl" }
{/if}

{if $active_modules.Banner_System and $right_banners ne ''}
  {include file="modules/Banner_System/banner_rotator.tpl" banners=$right_banners banner_location='R'}
{/if}

{include file="poweredby.tpl"}
