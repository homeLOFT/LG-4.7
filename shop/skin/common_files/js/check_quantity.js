/* vim: set ts=2 sw=2 sts=2 et: */
/**
 * Quantity checking script
 * 
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage JS Library
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com> 
 * @version    d5960331bcf93a12f89be438097ac50a2e861236, v4 (xcart_4_6_4), 2014-06-10 14:58:47, check_quantity.js, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

// Check quantity input box
function check_quantity(id, featured) {

  var inp = document.getElementById('product_avail_' + id + featured);
  if (!inp)
    return true;

  if (isNaN(inp.minQuantity))
    inp.minQuantity = products_data[id].min_quantity;

  if (isNaN(inp.maxQuantity))
    inp.maxQuantity = products_data[id].quantity;

  if (!isNaN(inp.minQuantity) && !isNaN(inp.maxQuantity)) {
    var q = parseInt(inp.value);
    if (isNaN(q)) {
      alert(substitute(lbl_product_quantity_type_error, "min", inp.minQuantity, "max", inp.maxQuantity));
      return false;
    }

    if (q < inp.minQuantity) {
      alert(substitute(lbl_product_minquantity_error, "min", inp.minQuantity));
      return false;
    }

    if (q > inp.maxQuantity && is_limit) {
      if (parseInt(inp.maxQuantity) == 0) {
        alert(txt_out_of_stock);
      } else {
        alert(substitute(lbl_product_maxquantity_error, "max", inp.maxQuantity));
      }
      return false;
    }
  }

  return true;
}

function change_quantity_input_box(inp_id, step, min_amount) {
  inp = document.getElementById(inp_id);
  if (!inp)
    return;
  inp.value = Math.round(parseInt(inp.value) + parseInt(step));
  if (inp.value <= '0')
    inp.value = 0;
    
  return true;
}
