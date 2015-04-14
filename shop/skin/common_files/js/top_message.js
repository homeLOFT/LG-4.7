/* vim: set ts=2 sw=2 sts=2 et: */
/**
 * Top message functions
 * 
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage JS Library
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com> 
 * @version    f2c96ddb72c96605401a2025154fc219a84e9e75, v4 (xcart_4_6_1), 2013-08-19 12:16:49, top_message.js, random
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

/* f2c96ddb72c96605401a2025154fc219a84e9e75, v4 (xcart_4_6_1), 2013-08-19 12:16:49, top_message.js, random */

function showTopMessage(content, type, anchor) {

  if (type === undefined) {
    type = 'I';
  }

  type = type.toLowerCase();

  $('#top-message').remove();

  if (type == 'e') {
    content = '<em>' + lbl_error + ':</em> ' + content;
  } else if (type == 'w') {
    content = '<em>' + lbl_warning + ':</em> ' + content;
  }

  var corners = ' ui-corner-bottom';
  var iframe_style = '';
  if (top !== self) {
    corners = ' ui-corner-all';
    iframe_style = ' class="inside-iframe"';
  }

  $('body').prepend('<div id="top-message"' + iframe_style + ' style="display: none;"><div class="box' + corners + ' message-' + type + '"><a href="javascript: void(0);" class="close-link" onclick="javascript: $(\'#top-message\').hide();"><img src="'+ images_dir + '/spacer.gif" class="close-img" /></a>' + content + '</div>');

  if (anchor) {
    $('#top-message > div.box').append('<div class="anchor"><a href="#' + anchor +'">' + lbl_go_to_last_edit_section + '<img src="' + images_dir + '/spacer.gif" alt="" /></a></div>');
  }

  if (topMessageDelay[type]) {
    $("#top-message").fadeIn('slow').delay(topMessageDelay[type]).fadeOut('slow');
  } else {
    $("#top-message").fadeIn('slow');
  }

}
