/* vim: set ts=2 sw=2 sts=2 et: */
/**
 * Voting widget
 * 
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage JS Library
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com> 
 * @version    cdba81f1d9d660646ce1fc230950c836b5cf4447, v2 (xcart_4_4_0_beta_2), 2010-05-27 14:09:39, vote_bar.js, igoryan
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

var creviews_hover_loaded;
if ($.browser.msie && !creviews_hover_loaded && parseInt($.browser.version) < 7) {
  creviews_hover_loaded = true;

  $(document).ready(
    function() {
      $('.creviews-rating-box .allow-add-rate li a').hover(
        function() {
          if (!this._parents)
            this._parents = $(this).parents('li').children('a');

          this._parents.addClass('over');
        },
        function() {
          if (!this._parents)
            this._parents = $(this).parents('li').children('a');

          this._parents.removeClass('over');
        }
      );

      return true;
    }
  );
}
