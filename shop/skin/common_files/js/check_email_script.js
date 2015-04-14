/* vim: set ts=2 sw=2 sts=2 et: */
/**
 * Check email script
 * 
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage JS Library
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com> 
 * @version    f2c96ddb72c96605401a2025154fc219a84e9e75, v4 (xcart_4_6_1), 2013-08-19 12:16:49, check_email_script.js, random
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

function checkEmailAddress(field, empty_err) {
  var err = false;

  if (!field) {
    return true;
  }

  if (field.value.length == 0) {
    if (empty_err != 'Y') {
      return true;
    } else {
      err = true;
    }
  }

  if (!err && field.value.replace(/^\s+/g, '').replace(/\s+$/g, '').search(email_validation_regexp) == -1) {
    err = true;
  }

  if (err) {
        markErrorField(field);
    xAlert(txt_email_invalid, '', 'W');
    field.focus();
    field.select();
  }

  return !err;
}

