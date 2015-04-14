{*
e3d566aa926319b6bc7ccc22ab9eacd31c55f836, v2 (xcart_4_4_0_beta_2), 2010-06-29 14:20:06, ajax.minicart.tpl, igoryan
vim: set ts=2 sw=2 sts=2 et:
*}
{if not ($smarty.cookies.robot eq 'X-Cart Catalog Generator' and $smarty.cookies.is_robot eq 'Y')}
{capture name=ajax_minicart}
var lbl_error = '{$lng.lbl_error|wm_remove|escape:javascript}';
var txt_minicart_total_note = '{$lng.txt_minicart_total_note|wm_remove|escape:javascript}';
{/capture}
{load_defer file="ajax_minicart" direct_info=$smarty.capture.ajax_minicart type="js"}
{load_defer file="js/ajax.minicart.js" type="js"}
{/if}
