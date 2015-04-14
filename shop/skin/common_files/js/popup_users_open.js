/* vim: set ts=2 sw=2 sts=2 et: */
/**
 * Pop up users
 * 
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage JS Library
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com> 
 * @version    51d9c851ebf639d73ef8041315dba58669edbe86, v5 (xcart_4_6_4), 2014-05-28 16:50:53, popup_users_open.js, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

function open_popup_users(form, format, force_submit, single_selection) {
  single_selection = (typeof single_selection == 'undefined') ? false : single_selection;
  return window.open ("popup_users.php?form="+form+"&format="+escape(format)+'&force_submit='+(force_submit ? "Y" : "")+'&single_selection='+(single_selection ? "Y" : ""), "selectusers", "width=810,height=550,toolbar=no,status=no,scrollbars=yes,resizable=no,menubar=no,location=no,direction=no");
}
