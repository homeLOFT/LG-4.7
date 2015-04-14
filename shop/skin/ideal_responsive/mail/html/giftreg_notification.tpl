{*
e94f191a2cc0cb8dbefd384fabaa6f48af5f3c11, v1 (xcart_4_6_2), 2013-12-25 09:14:58, giftreg_notification.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{if not $display_only_body}
  {config_load file="$skin_config"}
  {include file="mail/html/mail_header.tpl"}
  <table class="block-grid one-up">
    <tr>
      <td>
{/if}
{if $mail_data}
{$mail_data.message}
{else}
{$lng.eml_giftreg_notification}
{foreach from=$wl_products item=wl_product}
<hr size="1" noshade="noshade" />

{$wl_product.product}

<br />{$wl_product.descr|truncate:200:"..."}

<br />{$lng.lbl_price}: {currency value=$wl_product.price plain_text_message="Y"}

{/foreach}

{if $wl_giftcerts ne ""}

{foreach from=$wl_giftcerts item=gc key=gcindex}

{if $g.amount_purchased lte 1}
<hr size="1" noshade="noshade" />

<br />{$lng.lbl_gift_certificate}

<br />{$lng.lbl_recipient}: {$gc.recipient}

{if $gc.send_via eq "E"}
<br />{$lng.lbl_email}: {$gc.recipient_email}
{elseif $gc.send_via eq "P"}
<br />{$lng.lbl_mail_address}: 
<p/ >{$gc.recipient_address}, {$gc.recipient_city}, {if $config.General.use_counties eq "Y"}{$gc.recipient_countyname} {/if}{$gc.recipient_state}
<br />{$gc.recipient_country} {$gc.recipient_zipcode}
{if $gc.recipient_phone}
<br />{$lng.lbl_phone}: {$gc.recipient_phone}
{/if}
{/if}

<br />{$lng.lbl_amount}: {currency value=$gc.amount plain_text_message="Y"}

{/if}
{/foreach}
{/if}

<hr size="1" noshade="noshade" />

<br />{$lng.eml_giftreg_click_to_view}:

<br /><a href="{$catalogs.customer}/giftregs.php?eventid={$eventid}&amp;wlid={$wlid}">{$catalogs.customer}/giftregs.php?eventid={$eventid}&wlid={$wlid}</a>
{/if}
{if not $display_only_body}
    </td>
  </tr>
</table>
{include file="mail/html/signature.tpl"}
{/if}
