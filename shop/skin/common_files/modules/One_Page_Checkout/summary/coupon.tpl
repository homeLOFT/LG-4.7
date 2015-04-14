{*
ccede2f65deb8ae95e1d6b6fdc7056c93b8b430d, v5 (xcart_4_5_5), 2013-02-01 17:04:27, coupon.tpl, random 
vim: set ts=2 sw=2 sts=2 et:
*}
<div id="opc_coupon" class="coupon-info">

  <div id="coupon-applied-container"{if $cart.coupon eq ''} style="display:none;"{/if}>

    <strong>{$lng.lbl_discount_coupon_applied}</strong>
    <a class="dotted unset-coupon-link" href="cart.php?mode=unset_coupons" title="{$lng.lbl_unset_coupon|escape}">{$lng.lbl_unset_coupon|escape}</a>

  </div>

  <div id="couponform-container"{if $cart.coupon ne ''} style="display:none;"{/if}>

    <h3><a href="#" id="show_coupon_code">{$lng.lbl_redeem_discount_coupon}</a></h3>
    <div style="display: none;" id="coupon_code_container">
      <p>{$lng.txt_add_coupon_header}</p>

      <form action="cart.php" name="couponform">

        <input type="hidden" name="mode" value="add_coupon" />

        <label for="coupon">
          {$lng.lbl_coupon_code}:
          <input type="text" size="20" name="coupon" id="coupon" />
        </label>
        {include file="customer/buttons/button.tpl" type="input" style="image" onclick="return false;"}

      </form>

      <hr />
    </div>

  </div>

</div>

{capture name=coupon_code_toggle_js}
{literal}
  $('#show_coupon_code').click(function () {
    $('#coupon_code_container').toggle();

    return false;
  });
{/literal}
{/capture}
{load_defer file="coupon_code_toggle_js" direct_info=$smarty.capture.coupon_code_toggle_js type="js"}
