{*
16fd72c950254a8d4861815dac0d6147af93e43f, v8 (xcart_4_7_0), 2014-12-23 10:13:26, simple_products_list.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{list2matrix assign="products_matrix" assign_width="cell_width" list=$products row_length=$config.Appearance.simple_length}
{assign var="is_matrix_view" value=true}

{if $products_matrix}

  <table cellspacing="3" class="products products-table simple-products-table width-100" summary="{$lng.lbl_products_list|escape}">

    {foreach from=$products_matrix item=row name=products_matrix}

      <tr{interline name=products_matrix foreach_iteration="`$smarty.foreach.products_matrix.iteration`" foreach_total="`$smarty.foreach.products_matrix.total`"}>

        {foreach from=$row item=product name=products}
          {if $product}

            <td{interline name=products foreach_iteration="`$smarty.foreach.products.iteration`" foreach_total="`$smarty.foreach.products.total`" additional_class="product-cell"}>
              <div class="image">
                <div class="image-wrapper">
		<div class="imgborder" style="width: {$product.tmbn_x}px;" >
                  {if $active_modules.On_Sale}
                    {include file="modules/On_Sale/on_sale_icon.tpl" product=$product current_skin="books_and_magazines" module="simple_products_list"}
                  {else}
                    <a href="product.php?productid={$product.productid}"{if $open_new_window eq 'Y'} target="_blank"{/if} id="img_{$product.productid}" rel="#img_{$product.productid}_tooltip">{include file="product_thumbnail.tpl" productid=$product.productid image_x=$product.tmbn_x image_y=$product.tmbn_y product=$product.product tmbn_url=$product.tmbn_url}</a>
                  {/if}
		</div>
                </div>
              </div>

            {capture name=pt assign=txt}
		<b>{$product.product|amp}</b><br />
		<br />
              {if $product.product_type ne "C"}
                {if $product.appearance.is_auction}
                {else}
                  {if $product.taxed_price gt 0}
                      <span class="price-value">{currency value=$product.taxed_price}</span>
                  {/if}
                {/if}
              {/if}
            {/capture}
            {include file="customer/product_tip.tpl" text=$txt id="img_`$product.productid`" width="200"}
            </td>

          {/if}
        {/foreach}

      </tr>

      {if not $smarty.foreach.products_matrix.last}
        <tr class="separator">
          <td colspan="{$config.Appearance.products_per_row|default:1}">&nbsp;</td>
        </tr>
      {/if}

    {/foreach}

  </table>

{/if}

