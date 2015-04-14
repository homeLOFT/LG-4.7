{*
44c726a5f2719901cc8ad0f1101dcc0f40dd3c53, v1 (xcart_4_6_2), 2014-01-17 13:05:54, xps_subscription_status.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{include file="mail/mail_header.tpl"}


{if $subscription.status eq 'S'}
{if $by_admin eq 'Y'}{$lng.eml_xps_subscription_stopped_by_admin_info}{else}{$lng.eml_xps_subscription_stopped_info|substitute:attempts:$subscription.attempts}{/if}
{elseif $subscription.status eq 'A'}
{$lng.eml_xps_subscription_restarted_info}
{elseif $subscription.status eq 'F'}
{$lng.eml_xps_subscription_finished_info}
{/if}

{$lng.lbl_product_name}: {$product.product}
{$lng.lbl_xps_date_of_next_payment}: {$subscription.real_next_date|date_format:$config.Appearance.date_format}
{$lng.lbl_xps_subscription_fee}: {currency value=$subscription.fee} {alter_currency value=$subscription.fee}

{include file="mail/signature.tpl"}
