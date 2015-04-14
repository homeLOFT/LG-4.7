{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, lowlimit_warning_notification_admin.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{config_load file="$skin_config"}
{include file="mail/mail_header.tpl"}


{$lng.eml_lowlimit_warning_message|substitute:"sender":$config.Company.company_name:"productid":$product.productid}

{$lng.lbl_sku}: {$product.productcode}
{$lng.lbl_product}: {$product.product}
{if $product.product_options ne ""}
{$lng.lbl_selected_options}:
{include file="modules/Product_Options/display_options.tpl" options=$product.product_options options_txt=$product.product_options_txt is_plain="Y"}
{/if}

{$lng.lbl_items_in_stock|substitute:"items":$product.avail}


{include file="mail/signature.tpl"}
