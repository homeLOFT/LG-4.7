/* vim: set ts=2 sw=2 sts=2 et: */
/**
 * Abandoned Cart Reminder lib 
 * 
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage JS Library
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com> 
 * @version    $Id$
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

var email_pattern_regexp = new RegExp('abandoned_carts\\[\\S+@\\S+\\.\\S+\\]', 'gi');

function abcrPopulateOrderParams(email, form)
{
  if (!form)
    return;

  var email_field = document.getElementById("abcr-create-order-email");

  if (email_field != undefined) {
    email_field.value = email;
    return true;
  } else {
    return false;
  }
}

function abcrToggleCouponParamsTable(index)
{
  var table = '#abcr-coupon-params-' + index;
  var checkbox = document.getElementById('abcr-attach-coupon-' + index);

  if (checkbox.checked == true)
  {
    checkbox.value = 'on';
    $(table).show("slow");
  }
  else
  {
    checkbox.value = '';
    $(table).hide("slow");
  }

  return;
}
