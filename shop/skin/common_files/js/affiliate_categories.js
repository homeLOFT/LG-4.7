/* vim: set ts=2 sw=2 sts=2 et: */
/**
 * Expand affiliates tree in admin area
 * 
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage JS Library
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com> 
 * @version    ec2ca39cb71eff2cf859558b4f67f561a1b9efe4, v2 (xcart_4_4_0_beta_2), 2010-05-27 13:43:06, affiliate_categories.js, igoryan
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

function xaffCExpand(obj) {
  var ul = $('ul', obj.parentNode.parentNode).get(0);

  if (!ul)
    return true;

  if (ul.style.display == 'none') {
    ul.style.display = '';
    obj.innerHTML = '-';

  } else {
    ul.style.display = 'none';
    obj.innerHTML = '+';
  }

  return false;
}
