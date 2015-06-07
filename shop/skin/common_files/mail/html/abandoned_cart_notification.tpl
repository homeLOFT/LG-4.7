{*
$Id$
vim: set ts=2 sw=2 sts=2 et:
*}
{config_load file="$skin_config"}
{include file="mail/html/mail_header.tpl"}
<br /><br >
<h1>{$lng.eml_abcr_hello|substitute:"name":$customer.firstname}</h1>

{if $coupon}

{assign var="expiration_time" value=$coupon.expire|date_format:$config.Appearance.datetime_format}

{if $coupon.coupon_type eq 'free_ship'}
  {assign var="coupon_bonus" value=$lng.eml_abcr_notification_message_freeship}
{else}
  {if $coupon.coupon_type eq 'absolute'}
    {currency value=$coupon.discount assign="coupon_value"}
  {else}
    {assign var="coupon_value" value="`$coupon.discount`%"}
  {/if}
  {assign var="coupon_bonus" value=$lng.eml_abcr_notification_message_discount|substitute:"coupon_value":$coupon_value}
{/if}

{$lng.eml_abcr_notification_message|substitute:"coupon_bonus":$coupon_bonus|substitute:"expiration_time":$expiration_time|substitute:"coupon":$coupon.coupon|substitute:"company_name":$config.Company.company_name|substitute:"company_site":$http_location|nl2br}

{else} 

{* if no coupon assigned to the cart *}
{$lng.eml_abcr_notification_without_coupon_message|substitute:"company_name":$config.Company.company_name|substitute:"company_site":$http_location|nl2br}

{/if}

<br /><br />
{$lng.eml_abcr_cart_has_items}:
<br /><br />
<table border="0">
{foreach from=$products item=product}
<tr>
  <td valign="top">
    <img src="{$product.images.T.url}" width="{$product.images.T.new_x}" height="{$product.images.T.new_y}" />
  </td>
  <td valign="top">
    <a href="{$product.url}"><h3>{$product.product}</h3></a>
    <table border="0" cellspacing="5">
      <tr>
        <td>{$lng.lbl_quantity}:</td>
        <td><strong>{$product.amount}</td>
      </tr>
      <tr>
        <td>
          {$lng.lbl_price}:
        </td>
        <td><strong>{currency value=$product.price}</strong></td>
      </tr>
      {if $product.options}
        <tr>
          <td valign="top">{$lng.lbl_selected_options}:</td>
          <td valign="top">
            <strong>
            {foreach from=$product.options item=option}
              {$option.class}:&nbsp;{$option.option_name}<br />
            {/foreach}
            </strong>
          </td>
        </tr>
      {/if}
    </table>
  </td>
</tr>
{/foreach}

</table>

<br />
<h2>
{$lng.eml_abcr_visit_us_html|substitute:"return_link":$return_link}
</h2>
<br />
{include file="mail/html/signature.tpl"}
