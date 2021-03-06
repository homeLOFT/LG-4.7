{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, ask_question.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{include file="mail/html/mail_header.tpl"}

{capture name="row"}
{$lng.eml_someone_ask_question|substitute:"STOREFRONT":$current_location:"productid":$productid:"product_name":$product}:
{/capture}
{include file="mail/html/responsive_row.tpl" content=$smarty.capture.row}

<table class="block-grid data-table">

<tr>
<td colspan="2" class="section"><b>{$lng.lbl_customer_info}:</b></td>
</tr>

<tr>
<td class="name">{$lng.lbl_username}:</td>
<td class="value">{$uname|escape}</td>
</tr>

<tr>
<td class="name">{$lng.lbl_email}:</td>
<td class="value">{$email|escape}</td>
</tr>

{if $phone}
<tr>
<td class="name">{$lng.lbl_phone}:</td>
<td class="value">{$phone|escape}</td>
</tr>
{/if}

<tr>
<td colspan="2" class="section"><br /><b>{$lng.lbl_message}:</b><br /><hr size="1" noshade="noshade" color="#DDDDDD" align="left" /></td>
</tr>

<tr>
<td colspan="2"><i>{$question|escape|nl2br}</i></td>
</tr>
</table>

{include file="mail/html/signature.tpl"}
