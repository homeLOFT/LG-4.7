/* vim: set ts=2 sw=2 sts=2 et: */
/**
 * Override jQuery UI methods
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage JS Library
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @version    f1b47a4b73fa98aaa04170da287edb72673704a7, v1 (xcart_4_6_5), 2014-09-16 16:16:29, jquery_ui_override.js, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

/* http://stackoverflow.com/questions/14488774/using-html-in-a-dialogs-title-in-jquery-ui-1-10 */
$.widget("ui.dialog", $.extend({}, $.ui.dialog.prototype, {
    _title: function(title) {
        if (!this.options.title ) {
            title.html("&#160;");
        } else {
            title.html(this.options.title);
        }
    }
}));
