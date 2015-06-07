{*
2ef7fb52d397e834fc9f38fab3042a4f96941a06, v5 (xcart_4_7_2), 2015-04-27 17:07:51, order_invoice_print.tpl, mixon

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
{literal}
body {
    font-family: Verdana, Arial, Helvetica, Sans-serif;
    font-size: 11px;
    margin: 10px;
    padding: 10px;
}
{/literal}
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
{include file="mail/html/order_invoice.tpl" order=$orders_data[oi].order customer=$orders_data[oi].customer products=$orders_data[oi].products giftcerts=$orders_data[oi].giftcerts}

{if not $smarty.section.oi.last}
{$separator}
{/if}

{/section}
</body>
</html>
