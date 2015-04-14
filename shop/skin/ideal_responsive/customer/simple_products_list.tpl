{*
16fd72c950254a8d4861815dac0d6147af93e43f, v6 (xcart_4_7_0), 2014-12-23 10:13:26, simple_products_list.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{assign var="simple_length" value=$config.Appearance.simple_length}
{if $simple_length gt 6}
  {assign var="simple_length" value=6}
{/if}

{assign var="is_matrix_view" value=true}

<div class="products-div simple-products-div l{$simple_length}" id="responsive-products-list">

{foreach from=$products item=product name=products}

  {assign var=item_class value="item simple-product"}

  {section name=cell_count loop=$simple_length+2 start=1}
    {if ($smarty.foreach.products.iteration - 1) is div by $smarty.section.cell_count.index}
      {assign var=item_class value=$item_class|cat:" l`$smarty.section.cell_count.index`-first"}
    {/if}
  {/section}

  <div{interline name=products foreach_iteration="`$smarty.foreach.products.iteration`" foreach_total="`$smarty.foreach.products.total`" additional_class=$item_class}>
    <div class="item-box">
  
      <div class="image">
        <div class="image-wrapper"{if $config.Appearance.simple_thumbnail_height ne ''} style="height:{$config.Appearance.simple_thumbnail_height}px;"{/if}>
            {if $active_modules.On_Sale}
              {include file="modules/On_Sale/on_sale_icon.tpl" product=$product current_skin="ideal_responsive" module="simple_products_list"}
            {else}
              <a href="product.php?productid={$product.productid}"{if $open_new_window eq 'Y'} target="_blank"{/if}{if $config.Appearance.simple_thumbnail_height ne ''} style="height:{$config.Appearance.simple_thumbnail_height}px;{if $config.Appearance.simple_thumbnail_width ne ''} max-width:{$config.Appearance.simple_thumbnail_width*1.5}px;{/if}"{/if}>{include file="product_thumbnail.tpl" productid=$product.productid image_x=$product.tmbn_x image_y=$product.tmbn_y product=$product.product tmbn_url=$product.tmbn_url}</a>
            {/if}
        </div>
      </div>
      <div class="product-title">
        <script type="text/javascript">
          //<![CDATA[
          products_data[{$product.productid}] = {ldelim}{rdelim};
          //]]>
        </script>
        <a href="product.php?productid={$product.productid}" class="product-title"{if $open_new_window eq 'Y'} target="_blank"{/if}>{$product.product|amp}</a>
      </div>
      <div class="product-cell-price">
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
  
{/foreach}

</div>
<div class="clearing"></div>
