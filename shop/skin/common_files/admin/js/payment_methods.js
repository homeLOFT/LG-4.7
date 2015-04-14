/* vim: set ts=2 sw=2 sts=2 et: */
/**
 * Payment methods functions
 * 
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage JS Library
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com> 
 * @version    dfe80c625afd14b8bc5f289304302710aa6dc570, v1 (xcart_4_6_0), 2013-05-14 11:50:43, payment_methods.js, random
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

function markDisabledCB(obj) {
  $(obj).parents('table').eq(0).find(':checkbox:disabled').prop('checked', obj.checked);
}

function changeDisabledOrderBy(obj) {
  $(obj).parents('table').eq(0).find(':checkbox:disabled').parents('tr').eq(0).find(':text').filter(function() { return this.name.search(/orderby/) != -1; }).val(obj.value);
}
