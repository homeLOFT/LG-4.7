{*
b3f4306cfc5f7c0e0bf20dd7b0d9e6800d50b314, v3 (xcart_4_4_3), 2011-01-20 12:39:38, product_event.tpl, aim 
vim: set ts=2 sw=2 sts=2 et:
*}
{if $product.event_data ne ""}
{assign var=creator value="`$product.event_data.creator_title` `$product.event_data.firstname` `$product.event_data.lastname` (<a href=\"user_modify.php?user=`$product.event_data.userid`&amp;usertype=C\">`$product.event_data.login`</a>)"}
<tr>
  <td colspan="2" class="product-event">
    {$lng.lbl_giftreg_event_note|substitute:"event_name":$product.event_data.title:"eventid":$product.event_data.event_id:"customer":$product.event_data.login:"creator":$creator}</span>
  </td>
</tr>
{/if}
