{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, simple_products_list.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{assign var="is_matrix_view" value=true}

<div class="products products-list products-div simple-products-div">

{foreach from=$products item=product name=products}<div{interline name=products foreach_iteration="`$smarty.foreach.products.iteration`" foreach_total="`$smarty.foreach.products.total`" additional_class="item"}>
    <div class="item-box">

<script type="text/javascript">
  //<![CDATA[
  products_data[{$product.productid}] = {ldelim}{rdelim};
  //]]>
</script>

      <div class="image">
        <div class="image-wrapper"{if $config.Appearance.simple_thumbnail_height ne ''} style="height:{$config.Appearance.simple_thumbnail_height}px;"{/if}>
            {if $active_modules.On_Sale}
              {include file="modules/On_Sale/on_sale_icon.tpl" product=$product module="simple_products_list"}
            {else}
              <a href="product.php?productid={$product.productid}"{if $open_new_window eq 'Y'} target="_blank"{/if}{if $config.Appearance.simple_thumbnail_height ne ''} style="height:{$config.Appearance.simple_thumbnail_height}px;{if $config.Appearance.simple_thumbnail_width ne ''} max-width:{$config.Appearance.simple_thumbnail_width*1.5}px;{/if}"{/if}>{include file="product_thumbnail.tpl" productid=$product.productid image_x=$product.tmbn_x image_y=$product.tmbn_y product=$product.product tmbn_url=$product.tmbn_url}</a>
            {/if}
        </div>
      </div>
      <div class="details">
        <a href="product.php?productid={$product.productid}" class="product-title"{if $open_new_window eq 'Y'} target="_blank"{/if}>{$product.product|amp}</a>
        <div class="price-cell">
          {if $product.product_type ne "C"}
            {if $product.appearance.is_auction}
              <span class="price">{$lng.lbl_enter_your_price}</span><br />
              {$lng.lbl_enter_your_price_note}
            {else}
              {if $product.taxed_price gt 0}
                {if $active_modules.XPayments_Subscriptions and $product.subscription}
                  {include file="modules/XPayments_Subscriptions/customer/simple_products_list.tpl"}
                {else}
                <div class="price-row">
                  <span class="price-value">{currency value=$product.taxed_price}</span>
                </div>
                {/if}
              {/if}
            {/if}
          {else}
            &nbsp;
          {/if}
        </div>
      </div>

    </div>
    <div class="clearing"></div>
  </div>{/foreach}

</div>
<div class="clearing"></div>
