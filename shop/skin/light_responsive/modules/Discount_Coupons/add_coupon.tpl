{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, add_coupon.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}

{capture name=dialog}

  <a name='check_coupon'></a>
  <div class="add-coupon">
    <form action="cart.php" name="couponform">
      <input type="hidden" name="mode" value="add_coupon" />  
      <div class="data-name">
        <label for="coupon">{$lng.lbl_have_coupon_code}</label>
        <input type="text" class="text default-value" size="32" name="coupon" id="coupon" value="{$lng.lbl_coupon_code}" />
      </div>
      {include file="customer/buttons/submit.tpl" type="input"}
    </form>
  </div>
{/capture}
{if $page eq 'place_order'}
  {include file="customer/dialog.tpl" title=$lng.lbl_redeem_discount_coupon content=$smarty.capture.dialog additional_class="cart" noborder=true}
{else}
  {include file="customer/dialog.tpl" title=$lng.lbl_redeem_discount_coupon content=$smarty.capture.dialog additional_class="simple-dialog"}
{/if}
