/* vim: set ts=2 sw=2 sts=2 et: */
/**
 * Functions for Amazon Payments Advanced module
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage JS Library
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @version    0b8a1608ce2df638221cc019b8b19215808f314f, v2 (xcart_4_7_0), 2014-12-02 08:51:03, func.js, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

$(document).ready(function() {
    func_amazon_pa_put_button('payWithAmazonDiv_cart');
    func_amazon_pa_put_button('payWithAmazonDiv_checkout_methods');
});

function func_amazon_pa_check_address(orefid) {

  $('#opc_shipping').block();
  $('#opc_totals').block();

  $.post('amazon_checkout.php', {'mode': 'check_address', 'orefid': orefid}, function(data) {

    if (data == 'error') {
      xAlert(txt_ajax_error_note, lbl_error, 'E');
      return;
    }

    ajax.core.loadBlock($('#opc_shipping'), 'amazon_pa_shipping', {}, function() {

      $('#opc_shipping').find("input[name='shippingid']").change(function() {
        func_amazon_pa_on_change_shipping();
      });

      amazon_pa_address_selected = true;
      func_amazon_pa_check_checkout_button();
    });
    ajax.core.loadBlock($('#opc_totals'), 'amazon_pa_totals');
  });
}

function func_amazon_pa_check_payment(orefid) {
  amazon_pa_payment_selected = true;
  func_amazon_pa_check_checkout_button();
}

function func_amazon_pa_check_checkout_button() {
  if (amazon_pa_payment_selected && amazon_pa_address_selected) {
    // enable place order button
    $('button.place-order-button').removeClass('inactive');
    amazon_pa_place_order_enabled = true;
  }
}

function func_amazon_pa_on_change_shipping() {

  $('#opc_totals').block();

  var new_sid = $('#opc_shipping').find("input[type='radio']:checked").val();
  if (new_sid) {
    $.post('amazon_checkout.php', {'mode': 'change_shipping', 'shippingid': new_sid}, function(data) {
      ajax.core.loadBlock($('#opc_totals'), 'amazon_pa_totals');
    });
  }
}

function func_amazon_pa_place_order() {

  if (!amazon_pa_place_order_enabled) {
    return false;
  }

  // agreement
  var termsObj = $('#accept_terms')[0];
  if (termsObj && !termsObj.checked) {
    xAlert(txt_accept_terms_err, '', 'W');
    return false;
  }

  // prevent double submission
  amazon_pa_place_order_enabled = false;
  $.blockUI({
    message: '<span class="waiting being-placed">' + msg_being_placed + '</span>',
    css: {
      width: '450px',
      left:  $(window).width()/2-225
    }
  });

  // submit
  var co_form = $('#checkout_form');
  co_form.append('<input type="hidden" name="amazon_pa_orefid" value="'+amazon_pa_orefid+'" />');
  return true;
}

function func_amazon_pa_init_checkout() {

  $('button.place-order-button').addClass('inactive');
  $.blockUI.defaults.baseZ = 200000; // default is 1000 and it's too small

  new OffAmazonPayments.Widgets.AddressBook({
    sellerId: AMAZON_PA_CONST.SID,
    amazonOrderReferenceId: amazon_pa_orefid,

    onAddressSelect: function(orderReference) {
      func_amazon_pa_check_address(amazon_pa_orefid);
    },

    design: {
      size : {width:'400px', height:'260px'}
    },

    onError: function(error) {
      if (AMAZON_PA_CONST.MODE == 'test') {
        alert("Amazon AddressBook widget error: code="+error.getErrorCode()+' msg='+error.getErrorMessage());
      }
    }

  }).bind("addressBookWidgetDiv");

  new OffAmazonPayments.Widgets.Wallet({
    sellerId: AMAZON_PA_CONST.SID,
    amazonOrderReferenceId: amazon_pa_orefid,

    design: {
      size : {width:'400px', height:'260px'}
    },

    onPaymentSelect: function(orderReference) {
      func_amazon_pa_check_payment(amazon_pa_orefid);
    },

    onError: function(error) {
      if (AMAZON_PA_CONST.MODE == 'test') {
        alert("Amazon Wallet widget error: code="+error.getErrorCode()+' msg='+error.getErrorMessage());
      }
    }
  }).bind("walletWidgetDiv");

}

function func_amazon_pa_put_button(elmid) {

  if ($('#'+elmid).length <= 0) {
    return;
  }

  new OffAmazonPayments.Widgets.Button({
    sellerId: AMAZON_PA_CONST.SID,
    useAmazonAddressBook: true,
    onSignIn: function(orderReference) {
      var amazonOrderReferenceId = orderReference.getAmazonOrderReferenceId();
      window.location = 'amazon_checkout.php?amz_pa_ref=' + amazonOrderReferenceId;
    },
    onError: function(error) {
      if (AMAZON_PA_CONST.MODE == 'test') {
        alert("Amazon put button widget error: code="+error.getErrorCode()+' msg='+error.getErrorMessage());
      }
    }
  }).bind(elmid);
}
