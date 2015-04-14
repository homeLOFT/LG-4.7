{*
5e9713d172e2c9d249170e826dbebb812d2b8e54, v2 (xcart_4_6_2), 2014-01-10 13:34:30, xps_upcoming_rebill.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{include file="mail/mail_header.tpl"}


{include file="mail/salutation.tpl" title=$userinfo.title firstname=$userinfo.firstname lastname=$userinfo.lastname}

{$lng.eml_xps_upcoming_rebill_info|substitute:days:$config.XPayments_Subscriptions.xps_notification_days}

{$lng.lbl_product_name}: {$product.product}
{$lng.lbl_xps_date_of_next_payment}: {$subscription.real_next_date|date_format:$config.Appearance.date_format}
{$lng.lbl_xps_subscription_fee}: {currency value=$subscription.fee} {alter_currency value=$subscription.fee}

{include file="mail/signature.tpl"}
