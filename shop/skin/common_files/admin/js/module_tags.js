/* vim: set ts=2 sw=2 sts=2 et: */
/**
 * Module tags feature
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage JS Library
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com> 
 * @version    7ca20ac64e17c762b84ce0380e3656819ee54adf, v2 (xcart_4_6_0), 2013-05-07 14:45:49, module_tags.js, random
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */
var selectedTag = [];

function toggleTag(tag, type) {

  if (tag == selectedTag[type]) {
    return true;
  }

  selectedTag[type] = tag;
  $.cookie('xcart_selected_tag_'+type, tag);

  if (tag != 'all') {
    $('li[id^="li_'+type+'_"]:not(.'+tag+')').hide();
    $('li[id^="li_'+type+'_"].'+tag).show();
  } else {
    $('li[id^="li_'+type+'_"]').show();
    $('input[id^="tag_'+type+'_"]').attr('checked', false);
    $('input#tag_'+type+'_all').attr('checked', true);
    $('input[id^="tag_'+type+'_"]').button('refresh');
  }

}

$(document).ready(function() {
  $('input[id^="tag_"]').button();
  $('input[id^="tag_"]:checked').click();
});
