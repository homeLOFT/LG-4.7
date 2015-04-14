{*
a8db8e3a479caba8063b0536bdee66d8344ecaf9, v15 (xcart_4_6_5), 2014-10-10 16:04:37, home_main.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{if $smarty.get.mode eq "subscribed"}
{include file="customer/main/subscribe_confirmation.tpl"}

{elseif $smarty.get.mode eq "unsubscribed"}
{include file="customer/main/unsubscribe_confirmation.tpl"}

{elseif $main eq "returns"}
{include file="modules/RMA/customer/common.tpl"}

{elseif $main eq "register"}
{include file="customer/main/register.tpl"}

{elseif $main eq "profile_delete"}
{include file="customer/main/profile_delete_confirmation.tpl"}

{elseif $main eq "download"}
{include file="modules/Egoods/main.tpl"}

{elseif $main eq "send_to_friend"}
{include file="customer/main/send_to_friend.tpl"}

{elseif $main eq "pages"}
{include file="customer/main/pages.tpl"}

{elseif $main eq "manufacturers_list"}
{include file="modules/Manufacturers/customer_manufacturers_list.tpl"}

{elseif $main eq "manufacturer_products"}
{include file="modules/Manufacturers/customer_manufacturer_products.tpl"}

{elseif $main eq "search" or $main eq "advanced_search"}
{include file="customer/main/search_result.tpl"}

{elseif $main eq "cart"}
{include file="customer/main/cart.tpl"}

{elseif ($main eq "comparison" or $main eq 'choosing') and $active_modules.Feature_Comparison}
{include file="modules/Feature_Comparison/common.tpl"}

{elseif $main eq "wishlist" and $active_modules.Wishlist}
{include file="modules/Wishlist/wishlist.tpl"}

{elseif $main eq "order_message"}
{include file="customer/main/order_message.tpl"}

{elseif $main eq "order_message_widget"}
{include file="customer/main/order_message_widget.tpl"}

{elseif $main eq "orders"}
{include file="customer/main/search_orders.tpl"}

{elseif $main eq "history_order"}
{include file="customer/main/history_order.tpl"}

{elseif $main eq "product"}
{include file="customer/main/product.tpl"}

{elseif $main eq "giftcert"}
{include file="modules/Gift_Certificates/customer/giftcert.tpl"}

{elseif $main eq "new_arrivals" && $active_modules.New_Arrivals}
{include file="modules/New_Arrivals/new_arrivals.tpl"}

{elseif $main eq "on_sale" && $active_modules.On_Sale}
{include file="modules/On_Sale/on_sale.tpl" navigation="Y"}

{elseif $main eq "quick_reorder" && $active_modules.Quick_Reorder}
{include file="modules/Quick_Reorder/quick_reorder.tpl"}

{elseif $main eq "catalog" and $current_category.category eq ""}
{include file="customer/main/welcome.tpl"}

{elseif $main eq "catalog"}
{include file="customer/main/subcategories.tpl"}

{elseif $active_modules.Gift_Registry ne "" and $main eq "giftreg"}
{include file="modules/Gift_Registry/giftreg_common.tpl"}

{elseif $main eq "product_configurator"}
{include file="modules/Product_Configurator/pconf_common.tpl"}

{elseif $main eq "change_password"}
{include file="customer/main/change_password.tpl"}

{elseif $main eq "customer_offers"}
{include file="modules/Special_Offers/customer/offers.tpl"}

{elseif $main eq "customer_bonuses"}
{include file="modules/Special_Offers/customer/bonuses.tpl"}

{elseif $main eq "survey"}
{include file="modules/Survey/customer_survey.tpl"}

{elseif $main eq "surveys"}
{include file="modules/Survey/customer_surveys.tpl"}

{elseif $main eq "view_message"}
{include file="modules/Survey/customer_view_message.tpl"}

{elseif $main eq "view_results"}
{include file="modules/Survey/customer_view_results.tpl"}

{elseif $main eq "help"}

  {if $help_section eq "Password_Recovery"}
  {include file="customer/help/Password_Recovery.tpl"}

  {elseif $help_section eq "Password_Recovery_message"}
  {include file="customer/help/Password_Recovery_message.tpl"}

  {elseif $help_section eq "Password_Recovery_error"}
  {include file="customer/help/Password_Recovery.tpl"}

  {elseif $help_section eq "contactus"}
  {include file="customer/help/contactus.tpl"}

  {else}
  {include file="customer/help/general.tpl"}

  {/if}

{elseif $main eq "news_archive"}
{include file="modules/News_Management/customer/news_archive.tpl"}

{elseif $main eq "news_lists"}
{include file="modules/News_Management/customer/news_lists.tpl"}

{elseif $main eq "mc_news_archive"}
{include file="modules/Adv_Mailchimp_Subscription/customer/news_archive.tpl"}

{elseif $main eq "mc_news_lists"}
{include file="modules/Adv_Mailchimp_Subscription/customer/news_lists.tpl"}

{elseif $main eq "pages"}
{include file="customer/main/pages.tpl"}

{elseif $main eq "address_book"}
{include file="customer/main/address_book.tpl"}

{elseif $main eq "saved_cards" and $active_modules.XPayments_Connector}
{include file="modules/XPayments_Connector/saved_cards.tpl"}

{elseif $main eq "profile_delete"}
{include file="customer/main/profile_delete_confirmation.tpl"}

{elseif $main eq "profile_notdelete"}
{include file="customer/main/profile_notdelete_message.tpl"}

{elseif $main eq "cart_locked"}
{include file="customer/main/error_cart_locked.tpl"}

{elseif $main eq "giftreg_is_private"}
{include file="modules/Gift_Registry/error_giftreg_is_private.tpl"}

{elseif $main eq "error_no_shipping"}
{include file="customer/main/error_no_shipping.tpl"}

{elseif $main eq "delivery_error"}
{include file="customer/main/error_delivery.tpl"}

{elseif $main eq "error_ccprocessor_unavailable"}
{include file="customer/main/error_ccprocessor_unavail.tpl"}

{elseif $main eq "error_cmpi_error"}
{include file="customer/main/error_cmpi_error.tpl"}

{elseif $main eq "error_ccprocessor_error"}
{include file="customer/main/error_ccprocessor_error.tpl"}

{elseif $main eq "subscribe_bad_email"}
{include file="modules/News_Management/subscribe_bad_email.tpl"}

{elseif $main eq "disabled_cookies"}
{include file="customer/main/error_disabled_cookies.tpl"}

{elseif $main eq "403"}
{include file="customer/main/403.tpl"}

{elseif $main eq "authentication"}
{include file="customer/main/authentication.tpl" is_remember="Y"}

{elseif $main eq "reviews_list"}
{include file="modules/Advanced_Customer_Reviews/customer_reviews_list.tpl"}

{elseif $main eq "add_review"}
{include file="modules/Advanced_Customer_Reviews/customer_add_review.tpl"}

{elseif $main eq "tickets" and $active_modules.Kayako_Connector ne ""}
{include file="modules/Kayako_Connector/customer/tickets_main.tpl"}

{elseif $main eq "twofactor_token_verify" and $active_modules.TwoFactorAuth}
{include file="modules/TwoFactorAuth/customer/token_form.tpl"}

{elseif $main eq "xps_subscriptions" and $active_modules.XPayments_Subscriptions}
{include file="modules/XPayments_Subscriptions/customer/subscriptions.tpl"}

{elseif $main eq "linked_accounts" and $active_modules.XAuth ne ""}
{include file="modules/XAuth/linked_accounts.tpl"}

{elseif $main eq "testimonials_list"}
{include file="modules/Testimonials/customer_testimonials_list.tpl"}

{elseif $main eq "add_testimonial"}
{include file="modules/Testimonials/customer_add_testimonial.tpl"}

{else}

{include file="common_templates.tpl"}

{/if}
