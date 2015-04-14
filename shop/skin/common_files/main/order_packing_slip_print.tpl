{*
48555ac37d172f4c72987d68316aa905b9bd8c36, v1 (xcart_4_7_0), 2015-01-23 00:53:05, order_packing_slip_print.tpl, mixon

vim: set ts=2 sw=2 sts=2 et:
*}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>{strip}
{capture name=title}
{if $config.SEO.page_title_format eq "long_direct" or $config.SEO.page_title_format eq "short_direct"}
{section name=position loop=$location}
{if not $smarty.section.position.first}&nbsp;::&nbsp;{/if}
{$location[position].0|strip_tags|escape}
{/section}
{else}
{section name=position loop=$location step=-1}
{if not $smarty.section.position.first}&nbsp;::&nbsp;{/if}
{$location[position].0|strip_tags|escape}
{/section}
{/if}
{/capture}
{if $config.SEO.page_title_limit lte 0}
{$smarty.capture.title}
{else}
{$smarty.capture.title|replace:"&nbsp;":" "|truncate:$config.SEO.page_title_limit|replace:" ":"&nbsp;"}
{/if}
{/strip}</title>
{include file="meta.tpl"}
<style type="text/css">
<!--
body {
    font-family: Verdana, Arial, Helvetica, Sans-serif;
    font-size: 11px;
    margin: 10px;
    padding: 10px;
}
.packing-slip {
    width: 600px;
}
.company-logo {
    float: left;
}
.page-title {
    float: right;
    text-align: center;
}
.page-title h1 {
    font-size: 24px;
    text-transform: uppercase;
}
.page-title span {
    font-size: 10px;
}
.packing-info {
    clear: both;
    padding-top: 25px;
    padding-bottom: 25px;
}
.packing-info table {
    width: 100%;
}
.packing-info td,
.company-info td {
    vertical-align: top;
    padding: 5px;
}
.customer-info {
    border: 1px solid #ddd;
}
.bill-to td,
.ship-to td {
    vertical-align: top;
    padding: 5px;
}
.packing-details table {
    border-spacing: 0px;
    padding: 0px;
    width: 100%;
}
.packing-details th {
    background: #36525f;
    color: #fff;
    border-top: 1px solid #000;
    border-bottom: 1px solid #000;
    border-right: 1px solid #fff;
    padding: 5px;
}
.packing-details th:first-child {
    border-left: 1px solid #000;
}
.packing-details th:nth-child(2) {
    width: 50%;
}
.packing-details th:last-child {
    border-right: 1px solid #000;
}
.packing-details td {
    border-color: #000;
    border-bottom: 1px solid #000;
    border-right: 1px solid #000;
    padding: 5px;
}
.packing-details td:first-child {
    border-left: 1px solid #000;
}
.packing-details td:nth-child(3),
.packing-details td:nth-child(4) {
    text-align: center;
}
.packing-details td table td {
    border: none;
    white-space: nowrap;
}
.packing-details tr:last-child td:nth-child(1) {
    border-right: none;
}
.packing-details tr:last-child td:nth-child(1),
.packing-details tr:last-child td:nth-child(2) {
    border-left: none;
    border-bottom: none;
}
.packing-details tr:last-child td:nth-child(3) {
    background: #36525f;
    color: #fff;
}
.customer-message {
    margin-top: 10px;
    padding: 5px;
    border: 1px solid #000;
    background: #eee;
    height: 100px;
}
.thank-you-message {
    text-align: center;
}
-->
</style>
</head>
<body{$reading_direction_tag}>
{if $config.Appearance.print_orders_separated eq "Y"}
{assign var="separator" value="<div style='page-break-after: always;'></div>"}
{else}
{assign var="separator" value="<br /><hr size='1' noshade='noshade' /><br />"}
{/if}

{section name=oi loop=$orders_data}
{include file="mail/html/order_packing_slip.tpl" order=$orders_data[oi].order customer=$orders_data[oi].customer products=$orders_data[oi].products giftcerts=$orders_data[oi].giftcerts}

{if not $smarty.section.oi.last}
{$separator}
{/if}

{/section}
</body>
</html>
