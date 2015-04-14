{*
3ab72301751f8e120e7f7c7ba39dc43f13c581f4, v5 (xcart_4_6_2), 2013-12-26 12:41:50, ps_paypal_bml_button.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{if $paypal_link eq "logo"}

<img src="{$ImagesDir}/bml_logo.png" />

{elseif $paypal_link eq "text"}
  <a href="https://www.securecheckout.billmelater.com/paycapture-content/fetch?hash=AU826TU8&content=/bmlweb/ppwpsiw.html" target="_blank"><img src="https://www.paypalobjects.com/webstatic/en_US/btn/btn_bml_text.png" /></a>

{elseif $paypal_link eq "return"}

  {assign var="smarty_get_paymentid" value=$smarty.get.paymentid|escape:"html"}
  {include file="customer/buttons/button.tpl" button_title=$lng.lbl_modify href="`$current_location`/payment/ps_paypal_bml.php?mode=express&payment_id=`$smarty_get_paymentid`&do_return=1"}

{elseif $paypal_link eq "button"}

  <div class="paypal-cart-button paypal-bml-button">
    <div>
      <p>{$lng.lbl_or_use}</p>
      <form action="{$current_location}/payment/ps_paypal_bml.php" method="get" name="paypalbmlbuttonform" id="paypalbmlbuttonform">
        <input type="hidden" name="mode" value="express" />
        <input type="hidden" name="payment_id" value="{$paypal_bml_id}" />
        <div class="bml-button">
          <input type="image" src="https://www.paypalobjects.com/webstatic/en_US/btn/btn_bml_SM.png" />
        </div>
      </form>
      <a href="https://www.securecheckout.billmelater.com/paycapture-content/fetch?hash=AU826TU8&content=/bmlweb/ppwpsiw.html"><img src="https://www.paypalobjects.com/webstatic/en_US/btn/btn_bml_text.png" /></a>
    </div>
  </div>

{else}

  {capture name=paypal_express_dialog}

    <form action="{$current_location}/payment/ps_paypal_bml.php" method="get" name="paypalexpressform">
      <input type="hidden" name="mode" value="express" />
      <input type="hidden" name="payment_id" value="{$paypal_bml_id}" />
      <input type="image" src="https://www.paypalobjects.com/webstatic/en_US/btn/btn_bml_SM.png" />
    </form>

  {/capture}
  {include file="customer/dialog.tpl" title=$lng.lbl_checkout_with_paypal_express content=$smarty.capture.paypal_express_dialog}

{/if}
