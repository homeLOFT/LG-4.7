{*
ec03a20be20ceae24fb23dba03e15c75b5fdc4c7, v10 (xcart_4_6_2), 2013-10-17 11:14:06, modules.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{include file="page_title.tpl" title=$lng.lbl_modules}

{*
{$lng.txt_modules_top_text}

<br /><br />
*}
{include file="customer/main/ui_tabs.tpl" prefix="modules-tabs-" mode="inline" default_tab="-1last_used_tab" tabs=$modules_tabs}
