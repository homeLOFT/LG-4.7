{*
71ae8da1574e2d17ed36dafb837750799af54781, v1 (xcart_4_6_0), 2013-04-05 15:51:07, klarna_popup_addresses.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}

{if $klarna_addresses ne ''} 
  <div class="klarna-popup-title">
    <strong>{$lng.lbl_klarna_select_address}</strong>
  </div>
  <script type="text/javascript">
  //<![CDATA[
    {literal}
    function check_selected_address() {

      if ($("input[name=selected_address]").val() == '') {

        return false;
      }

      return true;
    }
    {/literal}
  //]]>
  </script>
  <form action="klarna_popup_address.php" method="post" name="klarnaaddressform" onsubmit="javascript: return ($('input[name=selected_address]:checked').val() != undefined)">
    <input type="hidden" name="mode" value="select" />
    
    <table class="klarna-address-table">
    {foreach from=$klarna_addresses key=id item=address}
      <tr>
        <td width="15" valign="top"><input type="radio" name="selected_address" value="{$id}" /></td>
        <td align="left" nowrap="nowrap">
          {if $address.firstname ne ''}
            {$lng.lbl_first_name}:<br />
          {/if}
          {if $address.lastname ne ''}
            {$lng.lbl_last_name}:<br />
          {/if}
          {$lng.lbl_city}:<br />
          {$lng.lbl_address}:<br />
          {$lng.lbl_zip_code}:<br />
          {$lng.lbl_country}:<br />
        </td>
        <td align="left" nowrap="nowrap">
          {if $address.firstname ne ''}
            {$address.firstname}<br />
          {/if}
          {if $address.lastname ne ''}
            {$address.lastname}<br />
          {/if}
          {$address.city}<br />
          {$address.address}<br />
          {$address.zipcode}<br />
          {$address.country}<br />
        </td>
      </tr>
    {/foreach}
    </table>
    <br />
    <div class="buttons-row buttons-auto-separator" align="center">
      {include file="customer/buttons/button.tpl" type="input" button_title=$lng.lbl_select}
      {include file="customer/buttons/button.tpl" button_title=$lng.lbl_back href="javascript: window.close();"}
    </div>

  </form>
{/if}

