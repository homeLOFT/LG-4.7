{*
b4cacf962df26166664426cdfdeeb1d33a17b0e2, v4 (xcart_4_7_0), 2015-02-13 11:29:46, tax_rates.tpl, mixon

vim: set ts=2 sw=2 sts=2 et:
*}
{if $taxes and $userinfo.tax_exempt ne "Y"}

    {foreach key=tax_name item=tax from=$taxes}
        {if $tax.tax_value gt 0}
            <small>
                {$tax.tax_display_name}
                {include file="main/display_tax_rate.tpl" value=$tax.rate_value assign="rate_value"}
                {if $tax.rate_type eq "%"}
                    {$rate_value}%
                {else}
                    {currency value=$tax.rate_value}
                {/if}
            </small>
            <br />
        {/if}
    {/foreach}

{/if}
