/* vim: set ts=2 sw=2 sts=2 et: */
/**
 * Paypal methods configuration
 * 
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage JS Library
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com> 
 * @version    381f2cb05e412f5b99f8bc7b904ced8c7c74d60a, v11 (xcart_4_5_5), 2013-01-17 11:46:51, ps_paypal_group.js, random
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

function view_solution(solution) {

  $('#pp_promo').html(pp_promo[solution]);

  if (
    !document.getElementById('sol_ipn')
    || !document.getElementById('sol_pro')
  ) {
    return false;
  }

  $('tr[id^="sol_"]:not(#sol_'+solution+')').hide();
  $('tr#sol_'+solution).show();

}

function changeExpressMethod()
{
  if (
    !document.getElementById('method_email')
    || !document.getElementById('method_api')
    || (!document.getElementById('method_email').checked && !document.getElementById('method_api').checked)
  ) {
    return false;
  }

  if (document.getElementById('method_email').checked) {
    $('#express_email').animate({opacity: 1.0}, 'fast', function() {
      $('#express_email').prop('disabled', false);
    });
    $('.api-setting').fadeOut('fast', function() {
      // Force hide due to a bug with fadeOut for elements with hidden parent
      $('.api-setting').hide();
    });

  } else {
    $('#express_email').animate({opacity: 0.25}, 'fast', function() {
      $('#express_email').prop('disabled', true);
    });
    $('.api-setting').fadeIn('fast', function() {
      $('.api-setting').show();
    });
  }

  return true;
}

$(document).ready(
  function() {
    $('#pp_promo').html(pp_promo[paypal_solution]);
    changeExpressMethod();
  }
);
