{*
e3d566aa926319b6bc7ccc22ab9eacd31c55f836, v2 (xcart_4_4_0_beta_2), 2010-06-29 14:20:06, ajax.rating.tpl, igoryan
vim: set ts=2 sw=2 sts=2 et:
*}
{if not ($smarty.cookies.robot eq 'X-Cart Catalog Generator' and $smarty.cookies.is_robot eq 'Y')}
{capture name="ajax_rating"}
var lbl_rated = '{$lng.lbl_rated|wm_remove|escape:javascript}';
var lbl_error = '{$lng.lbl_error|wm_remove|escape:javascript}';
var lbl_cancel_vote = '{$lng.lbl_cancel_vote|wm_remove|escape:javascript}';
{/capture}
{load_defer file="ajax_rating" direct_info=$smarty.capture.ajax_rating type="js"}
{load_defer file="modules/Customer_Reviews/ajax.rating.js" type="js"}
{/if}
