{*
99f49a017eeaa96cf5c4060c7785548523d6ad12, v4 (xcart_4_6_2), 2014-01-15 17:46:03, add_coupon.tpl, mixon
vim: set ts=2 sw=2 sts=2 et:
*}

{capture name=dialog}

  <a name='check_coupon'></a>
  <div class="add-coupon">
    <form action="cart.php" name="couponform">
      <input type="hidden" name="mode" value="add_coupon" />  
      <div class="data-name">
        {$lng.lbl_have_coupon_code}
        <input type="text" class="text default-value" size="32" name="coupon" value="{$lng.lbl_coupon_code}" />
      </div>
      {include file="customer/buttons/submit.tpl" type="input"}
    </form>
  </div>
{/capture}
{if $page eq 'place_order'}
  {include file="customer/dialog.tpl" title=$lng.lbl_redeem_discount_coupon content=$smarty.capture.dialog additional_class="cart" noborder=true}
{else}
  {include file="customer/dialog.tpl" title=$lng.lbl_redeem_discount_coupon content=$smarty.capture.dialog additional_class="simple-dialog" noborder=true}
{/if}
