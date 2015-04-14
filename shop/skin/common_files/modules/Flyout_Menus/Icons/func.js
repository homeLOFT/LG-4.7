/* vim: set ts=2 sw=2 sts=2 et: */
/**
 * Functions for the Flyout menus module
 * 
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage JS Library
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com> 
 * @version    2933c35a1cfb05fcc636843c0b93197c1809402a, v5 (xcart_4_6_2), 2013-11-07 18:53:27, func.js, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

function switchSubcatLayer(obj) {
  $(obj.parentNode).toggleClass('closed');
  return false;
}

$(document).ready(
  function() {
    if (typeof(window.catexp) != 'undefined' && catexp > 0) {
      $('.fancycat-icons-c #cat-layer-' + catexp).parents('li.closed').removeClass('closed');
    }
  }
);
