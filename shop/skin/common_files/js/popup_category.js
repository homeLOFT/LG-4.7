/* vim: set ts=2 sw=2 sts=2 et: */
/**
 * Popup category
 * 
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage JS Library
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com> 
 * @version    0e160a9c0ceea1a54a915ec09677760f55481cb6, v3 (xcart_4_4_0_beta_2), 2010-06-11 13:57:50, popup_category.js, igoryan
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

function popup_category(field_categoryid, field_category) {

  return popupOpen(
    'popup_category.php?field_categoryid=' + field_categoryid + '&field_category=' + field_category,
    '',
    {
      width: 800,
      height: 600,
      draggable: true
    }
  );
}
