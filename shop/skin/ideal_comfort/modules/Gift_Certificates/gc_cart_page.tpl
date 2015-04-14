{*
58139f621072a1b2de982957c1a16dd2c40d3232, v2 (xcart_4_5_5), 2013-02-11 17:31:46, gc_cart_page.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{if $giftcerts_data ne ""}

  {foreach from=$giftcerts_data item=gc key=gcindex}

      <tr class="separator top">
        <td colspan="5" class="first"><div class="clearing"></div></td>
        <td class="delete"></td>
      </tr>

      <tr class="giftcert-item">
        <td class="sep"><div class="clearing"></div></td>
        <td class="image">
          <img src="{$ImagesDir}/spacer.gif" alt="" />
        </td>
        <td class="details">

          <div class="product-title">{$lng.lbl_gift_certificate}</div>

          {if $g.amount_purchased gt 1}
            <div class="product-details-title">{$lng.lbl_purchased}</div>
          {/if}

          <div class="giftcert-item-row">
            <span class="giftcert-item-subtitle">{$lng.lbl_recipient}:</span>
            {$gc.recipient}
          </div>

          {if $gc.send_via eq "E"}
            <div class="giftcert-item-row">
              <span class="giftcert-item-subtitle">{$lng.lbl_email}:</span>
              {$gc.recipient_email}
            </div>
  
          {elseif $gc.send_via eq "P"}

            <div class="giftcert-item-row">
              <span class="giftcert-item-subtitle">{$lng.lbl_mail_address}:</span>
              {$gc.recipient_address}, {$gc.recipient_city}, {if $config.General.use_counties eq "Y"}{$gc.recipient_countyname} {/if}{$gc.recipient_state} {$gc.recipient_country} {include file="main/zipcode.tpl" val=$gc.recipient_zipcode zip4=$gc.recipient_zip4 static=true}
            </div>

            {if $gc.recipient_phone}
              <div class="giftcert-item-row">
                <span class="giftcert-item-subtitle">{$lng.lbl_phone}:</span>
                {$gc.recipient_phone}
              </div>
            {/if}

           {/if}

          <div class="giftcert-item-row">
            <span class="giftcert-item-subtitle">{$lng.lbl_amount}:</span>
            <span class="price">{currency value=$gc.amount}</span>
            <span class="market-price">{alter_currency value=$gc.amount}</span>
          </div>
          
          <div class="button-row">  
            {include file="customer/buttons/modify.tpl" href="giftcert.php?gcindex=`$gcindex`" additional_button_class="light-button edit-options" style=" "}
          </div>  

        </td>

        <td class="price giftcert-price">
            <span class="price product-price-text">{currency value=$gc.amount} x 1</span>
        </td>

        <td class="subtotal last"> 
            <span class="price">{currency value=$gc.amount}</span>
            <span class="market-price">{alter_currency value=$gc.amount}</span>
        </td>
        
        <td class="delete">

          <div class="delete-wrapper">
            {include file="customer/buttons/delete_item.tpl" href="giftcert.php?mode=delgc&gcindex=`$gcindex`" style="image" additional_button_class="simple-delete-button"}
          </div>
  
        </td>

      </tr>

      <tr class="separator">
        <td colspan="5" class="first"><div class="clearing"></div></td>
        <td class="delete"></td>
      </tr>

  {/foreach}

{/if}
