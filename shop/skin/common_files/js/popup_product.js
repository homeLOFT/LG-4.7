/* vim: set ts=2 sw=2 sts=2 et: */
/**
 * Popup product
 * 
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage JS Library
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com> 
 * @version    350c1de67ae3adfecfca4eb85c00b3b3f521d895, v4 (xcart_4_7_2), 2015-04-07 14:24:30, popup_product.js, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

function popup_product(field_productid, field_product, only_regular) {
  return popupOpen(
    'popup_product.php?field_productid=' + field_productid + '&field_product=' + field_product + '&only_regular=' + only_regular,
    '',
    { 
      width: Math.max($(this).width()-150, 800),
      maxWidth: Math.max($(this).width()-150, 800),
      height: 600,
      draggable: true
    }
  );
}
