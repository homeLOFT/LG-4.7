{*
5c31f6807b97e789cc88a6039bc6694680209cd2, v9 (xcart_4_6_0), 2013-04-24 10:29:55, common_templates.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{if $main eq "last_admin"}
{include file="main/error_last_admin.tpl"}

{elseif $main eq "product_disabled"}
{include file="main/error_product_disabled.tpl"}

{elseif $main eq "wrong_merchant_password"}
{include file="main/error_wrong_merchant_password.tpl"}

{elseif $main eq "cant_open_file"}
{include file="main/error_cant_open_file.tpl"}

{elseif $main eq "profile_delete"}
{include file="main/profile_delete_confirmation.tpl"}

{elseif $main eq "profile_notdelete"}
{include file="main/profile_notdelete_message.tpl"}

{elseif $main eq "classes"}
{include file="modules/Feature_Comparison/classes.tpl"}

{elseif $main eq "help"}
{include file="help/index.tpl" section=$help_section}

{elseif $main eq "xcart_news"}
{include file="main/xcart_news.tpl"}

{elseif $main eq "access_denied"}
{include file="main/error_access_denied.tpl"}

{elseif $main eq "permission_denied"}
{include file="main/error_permission_denied.tpl"}

{elseif $main eq "order_delete_confirmation"}
{include file="main/order_delete_confirmation.tpl"}

{elseif $main eq "product_delete_confirmation"}
{include file="main/product_delete_confirmation.tpl"}

{* WCM - Dynamic Product Tabs *}
{elseif $main eq "wcm_dynamictabs"}
{include file="modules/DynamicProductTabs/tabs_admin.tpl"}
{* / WCM - Dynamic Product Tabs *} 

{elseif $main eq "orders"}
{include file="main/orders.tpl"}

{elseif $main eq "create_order"}
{include file="main/create_order.tpl"}

{elseif $main eq "history_order"}
{include file="main/history_order.tpl"}

{elseif $main eq "product_modify"}
{include file="main/product_modify.tpl"}

{elseif $main eq "edit_file"}
{include file="admin/main/edit_file.tpl"}

{elseif $main eq "edit_dir"}
{include file="admin/main/edit_dir.tpl"}

{elseif $main eq "patch"}
{include file="admin/main/patch.tpl"}

{elseif $main eq "editor_mode"}
{include file="admin/main/editor_mode.tpl"}

{elseif $main eq "shipping_disabled"}
{include file="main/error_shipping_disabled.tpl"}

{elseif $main eq "realtime_shipping_disabled"}
{include file="main/error_realtime_shipping_disabled.tpl"}

{elseif $main eq "news_archive"}
{include file="modules/News_Management/news_archive.tpl"}

{elseif $main eq "news_lists"}
{include file="modules/News_Management/news_lists.tpl"}

{elseif $main eq "disabled_cookies"}
{include file="main/error_disabled_cookies.tpl"}

{elseif $main eq "demo_login_with_form"}
{include file="modules/Dev_Mode/login.tpl"}

{elseif $main eq "surveys"}
{include file="modules/Survey/surveys.tpl"}

{elseif $main eq "survey"}
{include file="modules/Survey/survey_modify.tpl"}

{elseif $main eq "quick_search"}
{include file="main/quick_search.tpl"}

{elseif $main eq "authentication"}
{include file="main/authentication.tpl" is_remember="Y"}

{elseif $main eq "reviews"}
{include file="modules/Advanced_Customer_Reviews/search_reviews.tpl"}

{elseif $main eq "review_modify"}
{include file="modules/Advanced_Customer_Reviews/admin_review.tpl"}

{elseif $template_main.$main ne ""}
{include file=$template_main.$main}

{elseif $main eq "product_notifications"}
{include file="modules/Product_Notifications/product_notifications_admin.tpl"}

{elseif $main eq 'twofactor_token_verify' and $active_modules.TwoFactorAuth}
{include file='modules/TwoFactorAuth/token_form.tpl'}

{else}

  {if $usertype eq 'C'}
  {include file="customer/main/error_page_not_found.tpl"}
  {else}
  {include file="main/error_page_not_found.tpl"}
  {/if}

{/if}
