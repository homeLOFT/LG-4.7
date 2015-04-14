{*
652406087d277cca28775ece05f2fd6290c36435, v8 (xcart_4_6_2), 2013-10-16 14:25:14, evaluation.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{if $is_enabled_evaluation_popup}
<script type="text/javascript">
//<![CDATA[
  {if $shop_evaluation eq 'WRONG_DOMAIN'}
    var _popup_sets = {ldelim}width:700,minHeight:425,closeOnEscape:true{rdelim};
  {else}
    var _popup_sets = {ldelim}width:700,minHeight:529,closeOnEscape:true{rdelim};
  {/if}
{literal}
$(document).ready(function () {
  return popupOpen('popup_info.php?action=evaluationPopup', '', _popup_sets);
});
{/literal}
//]]>
</script>
{/if}
