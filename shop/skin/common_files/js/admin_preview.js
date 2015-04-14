/* vim: set ts=2 sw=2 sts=2 et: */
/**
 * Demo preview functions
 * 
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage JS Library
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com> 
 * @version    6959437a96cdcd4bcfb47420a77ee0191d1b2842, v4 (xcart_4_6_2), 2013-09-20 11:26:08, admin_preview.js, random
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

$.event.add(
  window,
  "load",
  function() {
    $('form').unbind('submit').bind(
      'submit',
      function() {
        alert(txt_this_form_is_for_demo_purposes);
        return false;
      }
    );
    $('a:not([href*=#product-tabs]):not([id=product_modify_link])').unbind('click').bind(
      'click',
      function(e) {
        if (this.href && this.href.search(/javascript:/) != -1)
          return false;

        if (!e)
          e = event;

        if (e.stopPropagation)
          e.stopPropagation();
        else
          e.cancelBubble = true;

        alert(txt_this_link_is_for_demo_purposes);
        return false;
      }
    );
  }
);
