/* vim: set ts=2 sw=2 sts=2 et: */
/**
 * Cart page js functions
 * 
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage JS Library
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com> 
 * @version    9c470df45d56670dd8feb44585d193169151c6cd, v3 (xcart_4_5_2), 2012-07-23 05:31:43, cart.js, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

function updateCartItem(id) {
  if (!document.cartform)
    return false;

  var quantity = document.cartform.elements.namedItem('productindexes[' + id + ']');
  if (!quantity)
    return false;

  var url = 'cart.php?action=update&productindexes[' + id + ']=' + quantity.value;

  /* for Gift Registry module */
  var eventMark = document.cartform.elements.namedItem('event_mark[' + id + ']');
  if (eventMark) {
    url += '&event_mark[' + id + ']=' + eventMark.value;
  }

  if ($.browser.msie) {
    setTimeout(
      function() {
        self.location = url;
      },
      200
    );

  } else {
    self.location = url;
  }

  return false;
}

/**
 * 
 * Function is used for ideal_comfort skin when Wishlist module is enabled
 */
function move_to_wl(productid, cartid) {

  if (document.getElementById('productid'))
    document.getElementById('productid').value = productid;

  if (document.getElementById('action'))
    document.getElementById('action').value = '';
 
  if (document.getElementById('pindex'))
    document.getElementById('pindex').value = cartid;
    
  if (document.cartform) 
    submitForm(document.cartform, 'add2wl');
}


