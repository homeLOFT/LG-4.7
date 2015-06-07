{*
859c724068bc5ea8332a5711e6c0acf7f8de6a10, v18 (xcart_4_7_1), 2015-03-16 20:39:00, opc_profile.tpl, mixon

vim: set ts=2 sw=2 sts=2 et:
*}

<div id="opc_profile">

  {load_defer file="modules/One_Page_Checkout/lib/jquery.hide-show.min.js" type="js"}

  <h2>{$lng.lbl_name_and_address}</h2>
  <script type="text/javascript">
  //<![CDATA[
  // Used to update global $need_shipping var to work isCheckoutReady():ajax.checkout.js function properly
  var need_shipping = {if $need_shipping}true{else}false{/if};
  //]]>
  </script>
  
  {if ($userinfo ne '' and !$userinfo.is_incomplete) and not $reg_error and not $force_change_address}
    
    {include file="modules/One_Page_Checkout/profile/profile_details_html.tpl"}
  
  {else}
  
    {if $reg_error}
      <p class="error-message">{$reg_error.errdesc}</p>
    {/if}

    <form class="skip-auto-validation" action="cart.php?mode=checkout" method="post" name="registerform">
      <fieldset id="personal_details" class="registerform">

        <input type="hidden" name="usertype" value="C" />
        <input type="hidden" name="anonymous" value="{$anonymous}" />
  
        {include file='modules/One_Page_Checkout/profile/address_info.tpl' type='B' hide_header=true first=true}
  
        {include file='modules/One_Page_Checkout/profile/account_info.tpl' hide_header=true}
        
        {include file='modules/One_Page_Checkout/profile/address_info.tpl' type='S' hide_header=true first=true}
        
        {include file='modules/One_Page_Checkout/profile/personal_info.tpl' first=true}
  
        {include file='modules/One_Page_Checkout/profile/additional_info.tpl' section='A' first=true}
  
        {*** uncomment if you need to enable newsletter signup    
        {include file='modules/One_Page_Checkout/profile/newslist_info.tpl' hide_header=true}
        ***}

        {if $login ne ''}
            {assign var="button_label" value=$lng.lbl_save}
        {else}
            {assign var="button_label" value=$lng.lbl_submit}
        {/if}

        {include file="customer/buttons/button.tpl" button_title=$button_label additional_button_class="main-button update-profile" type="input" assign="submit_button"}

        {if $active_modules.Image_Verification and $show_antibot.on_registration eq 'Y' and $display_antibot}
            {include file="modules/Image_Verification/spambot_arrest.tpl" mode="simple" id=$antibot_sections.on_registration antibot_err=$reg_antibot_err button_code=$submit_button}
        {else}
        <div class="button-row" align="center">
            {$submit_button}
        </div>
        {/if}
  
      </fieldset>
    </form>
    
    {include file="check_registerform_fields_js.tpl" is_opc=true}
  
  {/if}

</div>
