/* vim: set ts=2 sw=2 sts=2 et: */
/**
 *
 * 
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Module
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com> 
 * @version    f87d7b74fe14656406d291fc0687ada3cd44391c, v2 (UNKNOWN_TAG), 2014-01-27 09:51:25, calendar.js, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

;(function ($, window, undefined) {
  var addLeadingZero = function (i) {
    return (i < 10 ? '0' : '') + i;
  };

  $(function () {
    $('.subscription-calendar').datepicker({
      numberOfMonths : [2,4],
      beforeShowDay : function (date) {
        var dates = $(this).data('dates');
        var dateClass = '';
        var dateInfo = '';
        var day = date.getFullYear() + '-' + addLeadingZero(date.getMonth() + 1) + '-' + addLeadingZero(date.getDate());

        if (undefined !== dates[day]) {
          dateClass = dates[day][0]

          if (undefined !== dates[day][1]) {
            dateInfo = dates[day][1]
          }
        }

        return [true, dateClass, dateInfo];
      },
      onSelect : function (dateStr, obj) {
        var dates = $(this).data('dates');
        var day = obj.selectedYear + '-' + addLeadingZero(obj.selectedMonth + 1) + '-' + addLeadingZero(obj.selectedDay);

        if (undefined !== dates[day] && dates[day][0] == 'subscription-done' || dates[day][0] == 'subscription-failed') {
          window.location.replace('order.php?orderid='+dates[day][2]);
        }
      }
    });
    $('a', '.subscription-calendar').addClass('external_link');
    $('.subscription-calendar').show();
  });
})(jQuery, window);
