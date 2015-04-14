{*
f46e8e61fad36fae692489c10ca85d60db9a626b, v3 (xcart_4_4_5), 2012-01-12 11:26:42, product_prices.tpl, ferz
vim: set ts=2 sw=2 sts=2 et:
*}
<div id="wl-prices"{if not $product_wholesale} style="display: none;"{/if}>

  {if $product.taxes}
    {capture name=taxdata}
      {include file="customer/main/taxed_price.tpl" taxes=$product.taxes display_info="N"}
    {/capture}
  {/if}

  <table cellspacing="1" summary="{$lng.lbl_wholesale_prices|escape}">

    <tr class="head-row">
      <th>{$lng.lbl_quantity}</th>
      <th>{$lng.lbl_price}{if $smarty.capture.taxdata|trim}*{/if}</th>
    </tr>

    {foreach from=$product_wholesale item=w name=wi}
      <tr>
        <td>
          {strip}
          {$w.quantity}{if $w.next_quantity eq 0}+{elseif $w.next_quantity ne $w.quantity}-{$w.next_quantity}{/if}
          &nbsp;
          {if $w.quantity eq "1"}
            {$lng.lbl_item}
          {else}
            {$lng.lbl_items}
          {/if}
          {/strip}
        </td>
        <td>{currency value=$w.taxed_price tag_id="wp`$smarty.foreach.wi.index`"}</td>
      </tr>
    {/foreach}

  </table>

  <div{if not $smarty.capture.taxdata|trim} style="display: none;"{/if}>
    <strong>*{$lng.txt_note}:</strong>{$smarty.capture.taxdata}
  </div>

</div>
