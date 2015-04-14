{*
b4cacf962df26166664426cdfdeeb1d33a17b0e2, v5 (xcart_4_7_0), 2015-02-13 11:29:46, taxed_price.tpl, mixon

vim: set ts=2 sw=2 sts=2 et:
*}
{if $taxes}

  {foreach key=tax_name item=tax from=$taxes}

    {if $tax.tax_value gt 0 and  $tax.display_including_tax eq "Y"}

      {if $display_info eq ""}
        {assign var="display_info" value=$tax.display_info}
      {/if}

      {$lng.lbl_including_tax|substitute:"tax":$tax.tax_display_name}

      {include file="main/display_tax_rate.tpl" value=$tax.rate_value assign="rate_value"}

      {if $display_info eq "V" or ($display_info eq "A" and $tax.rate_type eq "$")}

        {if not $is_subtax}
          {currency value=$tax.tax_value tag_id="tax_`$tax.taxid`"}
        {else}
          {currency value=$tax.tax_value}
        {/if}

      {elseif $display_info eq "R"}

        {if $tax.rate_type eq "$"}
          {currency value=$tax.rate_value}
        {else}
          {$rate_value}%
        {/if}

      {elseif $display_info eq "A"}

        {if $tax.rate_type eq "%"}
          {$rate_value}% (

          {if not $is_subtax}
            {currency value=$tax.tax_value tag_id="tax_`$tax.taxid`"}
          {else}
            {currency value=$tax.tax_value}
          {/if}
          )

        {/if}

      {/if}

      <br />

    {/if}

  {/foreach}

{/if}
