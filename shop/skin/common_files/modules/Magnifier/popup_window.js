/* vim: set ts=2 sw=2 sts=2 et: */
/**
 * Popup window functions
 * 
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage JS Library
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com> 
 * @version    cdba81f1d9d660646ce1fc230950c836b5cf4447, v2 (xcart_4_4_0_beta_2), 2010-05-27 14:09:39, popup_window.js, igoryan
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

/*
cdba81f1d9d660646ce1fc230950c836b5cf4447, v2 (xcart_4_4_0_beta_2), 2010-05-27 14:09:39, popup_window.js, igoryan
vim: set ts=2 sw=2 sts=2 et:
*/

function ResizeFlashMagnifier() {

  var fm = document.getElementById("flash_magnifier");
  if (fm) {

    var window_width = $(window).width();
    var window_height = $(window).height();

    if (window_width > 390)
      fm.width = window_width;

    if (window_height > 405)
      fm.height = window_height;
  }

  return true;
}

$.event.add(
  window,
  'load',
  function() {
    window.focus();
    ResizeFlashMagnifier();
    if ($.browser.msie && parseInt($.browser.version) == 7) {
      $(document.body).css('min-width', 'auto');
    }
  }
);

$.event.add(
  window,
  'resize',
  ResizeFlashMagnifier
);

