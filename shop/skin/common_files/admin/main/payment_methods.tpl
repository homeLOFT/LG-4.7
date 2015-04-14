{*
ec03a20be20ceae24fb23dba03e15c75b5fdc4c7, v10 (xcart_4_6_2), 2013-10-17 11:14:06, payment_methods.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}

{include file="page_title.tpl" title=$lng.lbl_payment_methods}

{include file="customer/main/ui_tabs.tpl" prefix="payment-tabs-" mode="inline" default_tab="-1last_used_tab" tabs=$payment_methods_tabs}

<script type="text/javascript" src="{$SkinDir}/admin/js/payment_methods.js"></script>
