{*
e94f191a2cc0cb8dbefd384fabaa6f48af5f3c11, v1 (xcart_4_6_2), 2013-12-25 09:14:58, acr_review_reminder.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{config_load file="$skin_config"}
{include file="mail/html/mail_header.tpl"}

{capture name="row"}
<h1>{include file="mail/salutation.tpl" title=$userinfo.title firstname=$userinfo.firstname lastname=$userinfo.lastname}</h1>

{$lng.txt_acr_add_products_reviews|substitute:"company_name":$config.Company.company_name}
<table width="100%" cellpadding="5" cellspacing="5" style="padding-top: 15px">

{section name=prod_num loop=$products}
<tr>

  <td style="border: 1px solid #EFEFEF; width: 10%; padding: 5px; text-align: center;">
  <a href="{$catalogs.customer}/product.php?productid={$products[prod_num].productid}">{include file="product_thumbnail.tpl" productid=$products[prod_num].productid image_x=$products[prod_num].image_x product=$products[prod_num].product tmbn_url=$products[prod_num].image_url}</a> 
  </td>

  <td>
  {$lng.lbl_acr_review_for} <a href="{$catalogs.customer}/add_review.php?productid={$products[prod_num].productid}&author={$fullname_url}">{$products[prod_num].product}</a>
  </td>
</tr>
{/section}

</table>
{/capture}
{include file="mail/html/responsive_row.tpl" content=$smarty.capture.row}

{include file="mail/html/signature.tpl"}
