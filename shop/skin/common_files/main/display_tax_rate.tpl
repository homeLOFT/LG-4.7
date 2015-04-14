{*
b4cacf962df26166664426cdfdeeb1d33a17b0e2, v3 (xcart_4_7_0), 2015-02-13 11:29:46, display_tax_rate.tpl, mixon

vim: set ts=2 sw=2 sts=2 et:
*}
{strip}
{assign var="rate_value2d" value="{$value|formatprice:false:false:2}"}
{assign var="rate_value3d" value="{$value|formatprice:false:false:3}"}
{if "{$rate_value2d}" eq "{$rate_value3d}"}
    {assign var="rate_value" value="{$rate_value2d}"}
{else}
    {assign var="rate_value" value="{$rate_value3d}"}
{/if}
{$rate_value}
{/strip}
