{*
7b6c178a7f306a61b2a9de249c201f7f301e0d6a, v4 (xcart_4_4_2), 2010-10-25 13:32:40, register_states.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{if $country_id eq ''}
  {assign var="country_id" value=$country_name}
{/if}
<span style="display:none;">
<input id="{$country_id}_state_value" type="text" value='{$state_value|strip_tags:false|escape}' />
<input id="{$country_id}_county_value" type="text" value='{$county_value|strip_tags:false|escape}' />
</span>
<script type="text/javascript">
//<![CDATA[
init_js_states(document.getElementById('{$country_id}'), '{$state_name}', '{$county_name}', '{$country_id}'{if $is_ajax_request or $is_modal_popup}, true{/if});
//]]>
</script>

