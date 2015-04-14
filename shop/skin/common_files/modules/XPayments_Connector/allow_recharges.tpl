{*
f7f946996b3f74495f07af7fc9812865f7e93875, v7 (xcart_4_6_5), 2014-08-04 14:12:36, allow_recharges.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{if 
  ($config.General.checkout_module eq 'Fast_Lane_Checkout' and $payment_data.use_recharges eq "Y")
  or ($config.General.checkout_module ne 'Fast_Lane_Checkout' and $payment.use_recharges eq "Y")
}
  {if $userinfo.id}
    <br/>
    <label class="xpc-save-card-label">
      {if $active_modules.XPayments_Subscriptions and $cart_has_subscriptions eq 'Y'}
      <input type="hidden" name="allow_save_cards" value="Y">
      {$lng.lbl_xps_force_save_cards}
      {else}
      <input type="checkbox" value="Y" name="allow_save_cards" {if $allow_save_cards eq "Y"}checked="checked"{/if}/>
      {$lng.lbl_allow_save_cards_checkout}
      {/if}
      <br/>
      <small><a href="javascript: void(0);" onclick="javascript: xAlert('{$lng.txt_save_cards_is_safe|escape:javascript}', '{$lng.lbl_information|escape:javascript}');">{$lng.lbl_save_cards_is_safe}</a></small>
    </label>
    <br/><br/>
  {else}
    <input type="hidden" name="allow_save_cards" value="N">
  {/if}
{/if}

