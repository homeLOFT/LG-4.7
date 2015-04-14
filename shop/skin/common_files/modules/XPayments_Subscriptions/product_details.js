/* vim: set ts=2 sw=2 sts=2 et: */
/**
 * XPayments Subscriptions - Product details js
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage JS Library
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @version    44c726a5f2719901cc8ad0f1101dcc0f40dd3c53, v1 (xcart_4_6_2), 2014-01-17 13:05:54, product_details.js, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

$(function () {

  var rebuildPeriods = function () {
    var type = $('#subscription_type').val();
    var periods = type == 'E' ? periods_E : periods_D;
    var periodsSelector = $('#subscription_period');
    var period = periodsSelector.val();

    periodsSelector.empty();
    for (var p in periods) if (periods.hasOwnProperty(p)) {
      periodsSelector.append('<option value="'+p+'">'+periods[p]+'</option>');
    }

    periodsSelector.val(period || selectedPeriod);
  }

  var checkNumber = function () {
    var type = $('#subscription_type').val();

    var period = $('#subscription_period').val();
    var number = $('#subscription_number').val();

    number = (isNaN(number) || number < 1) ? 1 : Math.abs(number);

    if (period == 'E') {
      if (period == 'W') {
        number = number <= 7 ? number : 7;
      } else if (period == 'M') {
        number = number <= 31 ? number : 31;
      } else if (period == 'Y') {
        number = number <= 366 ? number : 366;
      }
    }

    $('#subscription_number').val(number);
  }

  var checkNumberSuffix = function () {
    $('#subscription_number_suffix').toggle($('#subscription_type').val() == 'E');
  }

  var checkReverse = function () {
    var type = $('#subscription_type').val();

    $('#subscription_reverse_label').toggle(type == 'E');
    $('#subscription_reverse_note').toggle(type == 'E');

    if (type != 'E') {
      return;
    }

    var period = $('#subscription_period').val();
    var number = $('#subscription_number').val();

    $('#subscription_reverse_note .number').text(number);
    $('#subscription_reverse_note').toggle(period == 'M' && number > 28);
  }

  $('#subscription_type').change(function () {
    rebuildPeriods();
    checkNumber();
    checkNumberSuffix();
    checkReverse();
  });

  $('#subscription_number').change(function () {
    checkNumber();
    checkReverse();
  });

  $('#subscription_period').change(function () {
    checkNumber();
    checkReverse();
  })

  rebuildPeriods();
  checkNumber();
  checkNumberSuffix();
  checkReverse();
});
