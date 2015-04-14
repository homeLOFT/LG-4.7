{*
75e81aed7739b4fe5afcaf69020adcee08e39137, v2 (xcart_4_4_2), 2010-12-15 09:44:37, wishlist_send2friend.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{config_load file="$skin_config"}
{include file="mail/mail_header.tpl"}

{$lng.eml_hello}

{$lng.eml_send2friend|substitute:"sender":"`$userinfo.firstname` `$userinfo.lastname`"}

{$product.product}
===========================================
{$product.descr}

{$lng.lbl_price}: {currency value=$product.price}


{$lng.eml_click_to_view_product}:

{resource_url type="product" id=$product.productid}

{include file="mail/signature.tpl"}
