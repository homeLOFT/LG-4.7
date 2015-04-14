{*
71dff5d9c6d071bc9b9541029d46b1414daf1fa4, v1 (xcart_4_6_0), 2013-04-17 12:13:28, context_help.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
<script type="text/javascript">
//<![CDATA[
var contextHelpSettings = {ldelim}
	searchApiUrl:        window.location.protocol + '//cloudsearch.x-cart.com/help/search',
	searchAsYouType:  	 false,
	numExpandedResults:  3,
	widgetTitle:      '{$lng.lbl_help|wm_remove|escape:javascript}',
	inputPlaceholder: '{$lng.lbl_type_your_query|wm_remove|escape:javascript}',
	buttonTitle:      '{$lng.lbl_search|wm_remove|escape:javascript}',
	hideTabCaption:   '{$lng.lbl_hide_this_tab|wm_remove|escape:javascript}',
	autocorrectionTpl:'{$lng.lbl_searching_for_autocorrection|wm_remove|escape:javascript}',
	noResultsText:    '{$lng.lbl_no_results_found|wm_remove|escape:javascript}',
	connErrorText:    '{$lng.lbl_help_server_connection_error|wm_remove|escape:javascript}',
	hideHelpTabText:  '{$lng.lbl_hide_help_tab_text|wm_remove|escape:javascript}',
	seeMoreCaption:   '{$lng.lbl_see_n_more_results|wm_remove|escape:javascript}'
{rdelim};
//]]>
</script>
{load_defer file="lib/handlebars.min.js" type="js"}
{load_defer file="js/context_help.js" type="js"}
