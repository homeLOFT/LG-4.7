{*
63b14304b7080e4c3a8d1aa79cbfc7f1f1d03756, v1 (xcart_4_7_0), 2015-02-19 14:11:28, product_taxes.tpl, mixon

vim: set ts=2 sw=2 sts=2 et:
*}
{assign var='product_taxes' value=$product.taxes}
{if $product.taxes eq "" and $product.extra_data.taxes ne ''}
  {assign var='product_taxes' value=$product.extra_data.taxes}
{/if}
{if $product_taxes ne ""}
  <div class="aom-product-taxes">
    {foreach from=$product_taxes key=tax_name item=tax}
      {if $tax.tax_value gt 0}
        <div>
        {if $cart.product_tax_name eq ""}
          <span>{$tax.tax_display_name}</span>
        {/if}
          <span>
        {if $tax.rate_type eq "%"}
          {include file="main/display_tax_rate.tpl" value=$tax.rate_value}%
        {else}
          {currency value=$tax.rate_value}
        {/if}
          ({currency value=$tax.tax_value})
          </span>
        </div>
      {/if}
    {/foreach}
  </div>
{/if}
