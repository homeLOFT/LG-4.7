{*
8ec8b858917c921a726e2197071ce74d020e5ead, v1 (xcart_4_6_0), 2013-04-02 16:43:59, address.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
  
{if $av_error}

  {if $login ne ''}
    {assign var=av_script_url value="popup_address.php?id=`$id`"}
  {/if}

  {include file="modules/UPS_OnLine_Tools/register.tpl"}
  
{else}
  
  {include file="check_zipcode_js.tpl"}
  {include file="change_states_js.tpl"}
  
  {if $id gt 0}
    <h1>{$lng.lbl_edit_address}</h1>
  {else}
    <h1>{$lng.lbl_new_address}</h1>
  {/if}
  
  {if $reg_error ne ''}
    {include file="mark_required_fields_js.tpl" form="addressbook" errfields=$reg_error.fields}
    <p class="error-message">{$reg_error.errdesc}</p>
  {/if}
  
  <form action="popup_address.php" method="post" name="addressbook" onsubmit="javascript: return check_zip_code(this);"> 
  <input type="hidden" name="mode" value="{if $id gt 0}update{else}add{/if}" />
  <input type="hidden" name="id" value="{$id}" />
  <input type="hidden" name="for" value="{$for}" />
  
  <div class="address">
    
    {include file="customer/main/address_fields.tpl" address=$address name_prefix="posted_data" id_prefix=''}
  
    {if not $is_address_book_empty and $address.default_b ne 'Y'}
      <div class="address-checkbox">
        <label><input type="checkbox" id="default_b" name="posted_data[default_b]" size="32" maxlength="32"{if $address.default_b eq 'Y'} checked="checked"{/if}/>&nbsp;{$lng.lbl_use_as_b_address}</label>
      </div>
    {/if}
  
    {if not $is_address_book_empty and $address.default_s ne 'Y'}
      <div class="address-checkbox">
        <label><input type="checkbox" id="default_s" name="posted_data[default_s]" size="32" maxlength="32"{if $address.default_s eq 'Y'} checked="checked"{/if}/>&nbsp;{$lng.lbl_use_as_s_address}</label>
      </div>
    {/if}
  
    {if $is_address_book_empty}
      <input type="hidden" name="posted_data[default_b]" value="Y" />
      <input type="hidden" name="posted_data[default_s]" value="Y" />
    {/if}
  
  </div>
  <br />
  
  <div class="buttons-row buttons-auto-separator" align="center">
    {include file="customer/buttons/button.tpl" type="input" button_title=$lng.lbl_save}
    {if $return}
      {include file="customer/buttons/button.tpl" button_title=$lng.lbl_back href="javascript: popupOpen('popup_address.php?mode=`$return`&for=`$for`&type=`$type`'); return false;"}
    {/if}
  </div>
  
  </form>

{/if}
