{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, acr_review.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{include file="mail/html/mail_header.tpl"}

{include file="mail/html/responsive_row.tpl" content="`$lng.txt_acr_new_review`:"}

<table class="block-grid data-table">
  <tr>
    <td class="name">{$lng.lbl_date}:</td>
    <td class="value">{$review.datetime|date_format:$config.Appearance.datetime_format}</td>
  </tr>

  <tr>
    <td class="name">{$lng.lbl_acr_status}:</td>
    <td class="value">{include file="modules/Advanced_Customer_Reviews/review_status.tpl" review_status=$review.status static="Y"}</td>
  </tr>

  <tr>
    <td class="name">{$lng.lbl_product}:</td>
    <td class="value">
      <a href="{$catalogs.admin}/product_modify.php?productid={$review.productid}&amp;section=acr_reviews">{$review.product}</a>
    </td>
  </tr>


  <tr>
    <td class="name">{$lng.lbl_acr_author}:</td>
    <td class="value">{$review.author}</td>
  </tr>

  {if $review.userid gt 0}
  <tr>
    <td class="name">{$lng.lbl_customer}:</td>
    <td class="value"><a href="{$catalogs.admin}/user_modify.php?usertype=C&amp;user={$review.userid}">{$review.user}</a></td>
  </tr>
  {/if}

  {if $review.email ne ''}
  <tr>
    <td class="name">{$lng.lbl_email}:</td>
    <td class="value">{$review.email}</td>
  </tr>
  {/if}

  <tr>
    <td class="name">{$lng.lbl_acr_rating}:</td>
    <td class="value">{$review.rating}</td>
  </tr>

  <tr>
    <td class="name">{$lng.lbl_acr_comment}:</td>
    <td class="value">{$review.message|nl2br}</td>
  </tr>

  {if $config.Advanced_Customer_Reviews.acr_use_advantages_block eq 'Y'}
  <tr>
    <td class="name">{$lng.lbl_acr_advantages}:</td>
    <td class="value">{$review.advantages|nl2br}</td>
  </tr>

  <tr>
    <td class="name">{$lng.lbl_acr_disadvantages}:</td>
    <td class="value">{$review.disadvantages|nl2br}</td>
  </tr>
  {/if}

  <tr>
    <td colspan="2" class="section">&nbsp;</td>
  </tr>

  <tr>
    <td>
<table class="tiny-button skinned radius">
<tr>
<td>
<a href="{$catalogs.admin}/review_modify.php?review_id={$review.review_id}">{$lng.lbl_acr_edit_review}</a>
</td>
</tr>
</table>

    </td>
    <td></td>
  </tr>

</table>

{include file="mail/html/signature.tpl"}
