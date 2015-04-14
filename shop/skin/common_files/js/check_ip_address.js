/* vim: set ts=2 sw=2 sts=2 et: */
/**
 * IP address checking script
 * 
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage JS Library
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com> 
 * @version    ec2ca39cb71eff2cf859558b4f67f561a1b9efe4, v2 (xcart_4_4_0_beta_2), 2010-05-27 13:43:06, check_ip_address.js, igoryan
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

// Check IP address
function checkIPAddress(str, blockAlert) {
  var obj = false;
    if (typeof(str) != 'string' && str.tagName && str.tagName.toUpperCase() == 'INPUT') {
    obj = str;
        str = str.value;
  }

  if (typeof(str) != 'string')
    return false;

    var arr = str.split('.');
    var res = false;
    if (arr.length == 4) {
        res = true;
        var isMask = false;
        for (var i = 0; i < 4; i++) {
            if (arr[i] == '*') {
                isMask = true;

            } else if (arr[i].search(/^\d+$/) == -1 || parseInt(arr[i]) < 0 || parseInt(arr[i]) > 255 || isMask) {
                res = false;
                break;
            } 
        }
    }

    if (!res && !blockAlert && window.lbl_ip_address_format_incorrect) {
        markErrorField(obj);
        alert(lbl_ip_address_format_incorrect);
    if (obj && obj.focus)
      obj.focus();
  }

    return res;
}
