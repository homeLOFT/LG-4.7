/* vim: set ts=2 sw=2 sts=2 et: */
/**
 * Popup options
 * 
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage JS Library
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com> 
 * @version    cdba81f1d9d660646ce1fc230950c836b5cf4447, v2 (xcart_4_4_0_beta_2), 2010-05-27 14:09:39, edit_product_options.js, igoryan
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

function openPopupPOptions(target, id) {
    return window.open('popup_poptions.php?target='+target+'&id='+id,'POptions','width=400,height=350,toolbar=no,status=no,scrollbars=yes,resizable=no,menubar=no,location=no,direction=no');
}
