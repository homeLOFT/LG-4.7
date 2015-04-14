{*
75e81aed7739b4fe5afcaf69020adcee08e39137, v2 (xcart_4_4_2), 2010-12-15 09:44:37, giftreg_notification.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{if not $display_only_body}
{config_load file="$skin_config"}
{include file="mail/mail_header.tpl"}
{/if}
{if $mail_data}
{$mail_data.message|strip_tags}
{else}
{$lng.eml_giftreg_notification|strip_tags}

{section name=num loop=$wl_products}
===========================================
{$wl_products[num].product}

{$wl_products[num].descr|truncate:200:"..."}

{$lng.lbl_price}: {currency value=$wl_products[num].price}

{/section}
===========================================

{$lng.eml_giftreg_click_to_view}:

{$catalogs.customer}/giftregs.php?eventid={$eventid}&wlid={$wlid}
{/if}
{if not $display_only_body}
{include file="mail/signature.tpl"}
{/if}
