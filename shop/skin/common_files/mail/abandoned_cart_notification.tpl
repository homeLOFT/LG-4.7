{*
$Id$
vim: set ts=2 sw=2 sts=2 et:
*}
{config_load file="$skin_config"}
{include file="mail/mail_header.tpl"}
{$lng.eml_abcr_hello|substitute:"name":$customer.firstname}

{if $coupon}
{assign var="expiration_time" value=$coupon.expire|date_format:$config.Appearance.datetime_format}
{if $coupon.coupon_type eq 'free_ship'}
{assign var="coupon_bonus" value=$lng.eml_abcr_notification_message_freeship}
{else}
{if $coupon.coupon_type eq 'absolute'}{currency value=$coupon.discount assign="coupon_value"}{else}{assign var="coupon_value" value="`$coupon.discount`%"}{/if}
{assign var="coupon_bonus" value=$lng.eml_abcr_notification_message_discount|substitute:"coupon_value":$coupon_value}
{/if}
{$lng.eml_abcr_notification_message|substitute:"coupon_bonus":$coupon_bonus|substitute:"expiration_time":$expiration_time|substitute:"coupon":$coupon.coupon|substitute:"company_name":$config.Company.company_name|substitute:"company_site":$http_location}
{else}{* if no coupon assigned to the cart *}
{$lng.eml_abcr_notification_without_coupon_message|substitute:"company_name":$config.Company.company_name|substitute:"company_site":$http_location}
{/if}

{$lng.eml_abcr_cart_has_items}:
{foreach from=$products item=product}

{$lng.lbl_product}: {$product.product}{if $product.amount gt 1} x {$product.amount}{/if} 
{$lng.lbl_price}: {currency value=$product.price}
{if $product.options}
{$lng.lbl_selected_options}:
  {foreach from=$product.options item=option}
  {$option.class}: {$option.option_name}
  {/foreach}
{/if}
{/foreach}
-----

{$lng.eml_abcr_visit_us|substitute:"return_link":$return_link}

{include file="mail/signature.tpl"}
