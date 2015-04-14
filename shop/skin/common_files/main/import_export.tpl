{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, import_export.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{if $mode eq "export"}
{include file="page_title.tpl" title=$lng.lbl_export_data}

{else}
{include file="page_title.tpl" title=$lng.lbl_import_data}
{/if}

{$lng.txt_import_data_top_text}

<br /><br />

<br />

{if $mode eq "export"}
{include file="main/export.tpl"}

{else}
{include file="main/import.tpl"}
{/if}

