{*
bf37aba6ddbd4a569892cd51ec25e187e1978628, v2 (xcart_4_5_3), 2012-09-21 08:03:22, ups.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{include file="page_title.tpl" title=$lng.lbl_ups_online_tools}

{$lng.txt_ups_online_tools_top_text}

<br /><br />
{if $mode eq "rss"}
{include file="modules/UPS_OnLine_Tools/ups_rss.tpl"}
{elseif $ups_reg_step eq 0}
{include file="modules/UPS_OnLine_Tools/ups_main.tpl"}
{elseif $ups_reg_step eq 1}
{include file="modules/UPS_OnLine_Tools/ups_access_license_1.tpl"}
{elseif $ups_reg_step eq 2}
{include file="modules/UPS_OnLine_Tools/ups_access_license_2.tpl"}
{elseif $ups_reg_step eq 3}
{include file="modules/UPS_OnLine_Tools/ups_access_license_3.tpl"}
{elseif $ups_reg_step eq 4}
{include file="modules/UPS_OnLine_Tools/ups_access_license_4.tpl"}
{/if}
