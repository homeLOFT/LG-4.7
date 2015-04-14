/* vim: set ts=2 sw=2 sts=2 et: */
/**
 * Next-Previous products
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage JS Library
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @version    a8caf1146a82c7e25bc23adbf5c4c2eea754bed1, v1 (xcart_4_6_0), 2013-05-24 14:56:30, np_products.js, random
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */
$('.np-products ul li a').hover(
  function() {
    what = $(this).attr('class');
    if (!npProducts[what]['loaded']) {
      npProducts[what]['loaded'] = true;
      ajax.core.loadBlock(
        $('#np-popup-'+what),
        'np_product',
        {productid: npProducts[what]['id']}
      );
    }
    $('#np-popup-'+what).stop(true, true).fadeIn(100);
  },
  function() {
    $('#np-popup-'+$(this).attr('class')).delay(100).fadeOut(100);
  }
);
$('.np-products .popup').hover(
  function() {
    $(this).stop(true, true).show();
  },
  function() {
    $(this).delay(100).fadeOut(100);
  }
);
