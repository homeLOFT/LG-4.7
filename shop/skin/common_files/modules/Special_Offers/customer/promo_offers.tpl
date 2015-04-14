{*
cfa10c376a9d6022aa34e9c63f7071a86126e1a0, v4 (xcart_4_6_4), 2014-04-21 09:47:22, promo_offers.tpl, mixon
vim: set ts=2 sw=2 sts=2 et:
*}
{if $products and $cart.promo_offers}

  {capture name=dialog}

  {foreach name=offers from=$cart.promo_offers item=offer}

  {if $offer.promo_items_amount}

  <div>

    {if $offer.html_items_amount}
      {$offer.promo_items_amount}
    {else}
      <tt>{$offer.promo_items_amount|escape}</tt>
    {/if}

  </div>

  <div><img src="{$ImagesDir}/spacer.gif" width="1" height="10" alt="" /></div>

  <div>

    {if $offer.exceed_amount gt 0}
      <strong>{$lng.lbl_sp_get_this_offer}:</strong>

      <br /><img src="{$ImagesDir}/spacer.gif" width="1" height="5" alt="" /><br />

      {capture name="exceed_amount"}{currency value=$offer.exceed_amount}{/capture}
      {assign var="link" value="<a href=\"home.php\">`$smarty.capture.exceed_amount`</a>"}
      {$lng.lbl_sp_buy_more|substitute:"amount":$link}<br />

      <div><img src="{$ImagesDir}/spacer.gif" width="1" height="5" alt="" /></div>
    {/if}

    {if $offer.promo_data}
      {$lng.lbl_sp_purchase_following_products}:<br />
      <ul>
      {foreach name="prod_sets" from=$offer.promo_data item=product_sets}
        {foreach from=$product_sets item=param}
          {if $param.param_type eq "P"}
            <li><a href="product.php?productid={$param.param_id}">{$param.product}</a> <span class="small-note">[{$param.param_qnty} {$lng.lbl_sp_items}]</span></li>
          {elseif $param.param_type eq "C"}
            {assign var="link" value="<a href=\"home.php?cat=`$param.param_id`\">`$param.category`</a>"}
            <li>{$lng.lbl_sp_products_from_cat_s|substitute:"cat":$link} <span class="small-note">[{$param.param_qnty} {$lng.lbl_sp_items}]</span></li>
          {/if}
        {/foreach}
        {if not $smarty.foreach.prod_sets.last}
          - {$lng.lbl_or} - 
        {/if}
      {/foreach}
      </ul>
    {/if}

  </div>

  {if not $smarty.foreach.offers.last}
  <div><img src="{$ImagesDir}/spacer.gif" width="1" height="30" alt="" /></div>
  {/if}

  {/if}

  {/foreach}

  {/capture}
  {include file="customer/dialog.tpl" title=$lng.lbl_sp_also_review_offers|escape content=$smarty.capture.dialog}

{/if}
