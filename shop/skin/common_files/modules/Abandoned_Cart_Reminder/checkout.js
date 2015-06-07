/* vim: set ts=2 sw=2 sts=2 et: */
/**
 * Abandoned Cart Reminder lib 
 * 
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage JS Library
 * @author     Vladimir Petrov <random@x-cart.com> 
 * @version    $Id$
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

function func_abcr_save_cart() {

  func_abcr_clear_timeout();

  var email$ = $('input#email');
  if (email$.val() !== 'undefined') {
    var email = email$.val();
  }


  if (email && (email.replace(/^\s+/g, '').replace(/\s+$/g, '').search(email_validation_regexp) != -1)) {

    ship2diffChecked = ($('input#ship2diff').is(':checked')) ? 'Y' : '';

    send = {user_email: email, mode: 'save_cart', ship2diff: ship2diffChecked};

    $('[name^=address_book]').each(function() {
      if ($(this).val()) {
        send[$(this).attr('name')] = $(this).val();
      }
    });

    $.post(xcart_web_dir + '/abandoned_cart_in.php', send);
  }

}

function func_abcr_clear_timeout() {
    if (typeof(window.abcr_delayer) != 'undefined') {
      clearTimeout(window.abcr_delayer);
    }
}

function func_abcr_set_timeout() {
    func_abcr_clear_timeout();
    window.abcr_delayer = setTimeout(func_abcr_save_cart, 2000);
}

$(document).ready(function(){

  var email$ = $('input#email');

  if (email$.val() !== 'undefined') {
    // Only check for email when entry field appear at start (guarantees that email was not entered at all yet)
    $('input#email, [name^=address_book]').bind('change', func_abcr_set_timeout);
    $('input#email, [name^=address_book]').bind('input', func_abcr_set_timeout);
    $('input#ship2diff').click(func_abcr_set_timeout);
    $(window).bind('beforeunload', func_abcr_save_cart);
  }

});
