{*
4182101b6b98229d35c8bcb53c0469e2a06fbe95, v4 (xcart_4_5_4), 2012-10-08 09:50:51, address_book.tpl, random 
vim: set ts=2 sw=2 sts=2 et:
*}
{if $mode eq 'select'}
  <h1>{$lng.lbl_select_address}</h1>
  <div><a href="address_book.php"{if $is_modal_popup} onclick="javascript: self.location='address_book.php';"{/if}>{$lng.lbl_edit_address_book}</a></div>
{else}
  <h1>{$lng.lbl_address_book}</h1>
{/if}

<br />

<ul class="address-container{if $mode eq 'select'} popup-address{/if}">
  {include file="customer/main/address_box.tpl" add_new=true}
  {if $addresses}
    {foreach from=$addresses item=a key=addressid}
      {include file="customer/main/address_box.tpl" address=$a additional_fields=$address_book_additional_fields.$addressid}
    {/foreach}
  {/if}
</ul>
