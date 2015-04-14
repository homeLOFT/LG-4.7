/**
 * PayPal Express In-cotext Checkout controller
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage JS Library
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @version    fb087bc697b94c4301847784dc8f03e85e64d722, v2 (xcart_4_7_0), 2015-01-14 10:51:26, paypal_express_incontext.js, mixon
 *
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

(function (d, s, id) {
    var js, ref = d.getElementsByTagName(s)[0];
    if (!d.getElementById(id)) {
        js = d.createElement(s);
        js.id = id;
        js.async = true;
        js.src = "//www.paypalobjects.com/js/external/paypal.v1.js";
        ref.parentNode.insertBefore(js, ref);
    }
}(document, "script", "paypal-js"));

var paypalExpressCheckout = function (element) {

  // close all othet dialogs
  $('.ui-dialog-content').dialog('close').dialog('destroy').remove();

  PAYPAL.apps.Checkout.startFlow();
  element.target = "PPFrame";

  var t = setInterval(function () {
    if (!PAYPAL.apps.Checkout.isOpen()) {
      clearInterval(t);
      $('body').removeClass('PPFrame');
    }
  }, 500);

  return true;
};
