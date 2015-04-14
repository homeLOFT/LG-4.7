{*
0ed096c8fd2dcaaab50cc1d0f63c69083f0f8536, v10 (xcart_4_5_2), 2012-07-23 07:25:30, gift_wrapping_cart.tpl, aim 
vim: set ts=2 sw=2 sts=2 et:
*}
{if $display_giftwrap_section}
<div class="giftwrapping-cart cart-border">
  <div class="giftwrap-option">
    <label for="need_giftwrap">
      <input type="checkbox" id="need_giftwrap" name="need_giftwrap" value="Y"{if $cart.need_giftwrap eq "Y"} checked="checked"{/if} />
      {$lng.lbl_giftreg_use_wrapping}{if $cart.taxed_giftwrap_cost gt 0} ({currency value=$cart.taxed_giftwrap_cost display_sign=1}){/if}
    </label>
  </div>
  {if $cart.taxed_giftwrap_cost gt 0 and not $single_mode and $config.General.sum_up_wrapping_cost eq "Y" and $cart.is_multiorder eq "Y"}
    <div class="giftwrap-cart-note">{$lng.lbl_giftreg_sum_up_cost_note}</div>
  {/if}
  {if $config.General.enable_greeting_message eq 'Y'}
    <div class="giftwrap-message-text" id="giftrap_message"{if $cart.need_giftwrap neq "Y"} style="display: none;"{/if}>
      <div class="giftwrap-message-label">
        {$lng.lbl_giftreg_add_message}:
      </div>
      <textarea class="message-text" name="giftwrap_message" rows="5" cols="20">{$cart.giftwrap_message|escape}</textarea>
   </div>
  {/if}
  
  <div class="button-row">
    {include file="customer/buttons/button.tpl" button_title=$lng.lbl_giftreg_update_giftwrap additional_button_class="light-button" href="javascript: $('input[name=action]', this.form).val('giftwrap_update'); this.form.submit();"}
  </div>

</div>
<hr />
{/if}

{literal}
<script type="text/javascript">
//<![CDATA[
$("#need_giftwrap").bind('click',
  function(e) {
    if ($(this).is(':checked') && !$('#giftrap_message').is(':visible'))
      $('#giftrap_message').fadeIn('fast');
    else if ($('#giftrap_message').is(':visible'))
      $('#giftrap_message').fadeOut('fast');
  }
);
//]]>
</script>
{/literal}
