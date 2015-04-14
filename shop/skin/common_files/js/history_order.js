/* vim: set ts=2 sw=2 sts=2 et: */
/**
 * History order scripts
 * 
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage JS Library
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com> 
 * @version    ec2ca39cb71eff2cf859558b4f67f561a1b9efe4, v2 (xcart_4_4_0_beta_2), 2010-05-27 13:43:06, history_order.js, igoryan
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

/* ec2ca39cb71eff2cf859558b4f67f561a1b9efe4, v2 (xcart_4_4_0_beta_2), 2010-05-27 13:43:06, history_order.js, igoryan */

function switch_details_mode(edit_mode, cur_btn, old_btn) {
  var dv = document.getElementById("details_view");
  var de = document.getElementById("details_edit");

  if (!dv || !de || edit_mode == details_mode)
    return;

  if (edit_mode) {
    dv.style.display = 'none';
    de.style.display = '';

  } else {
      var rval = de.value;
      for (var of in details_fields_labels) {
      if (hasOwnProperty(details_fields_labels, of))
            rval = rval.replace(new RegExp(of, "g"), details_fields_labels[of]);
      }
      dv.value = rval;

    dv.style.display = '';
    de.style.display = 'none';
  }

  details_mode = edit_mode;
  cur_btn.style.fontWeight = 'bold';
  old_btn.style.fontWeight = '';
}
