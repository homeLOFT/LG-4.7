{*
cee59d0d93e7dca1f7929aab0cb1a35174131e59, v2 (xcart_4_6_4), 2014-06-30 12:01:19, admin_hidden_options.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
<div id="hidden-options" style="display: none;">
{foreach from=$xpc_configuration item=options_list key=section_name}
  {if $section_name ne $skip}
    {include file="modules/XPayments_Connector/admin_options_list.tpl" options_list=$options_list hidden=true}
  {/if}
{/foreach}
</div>
