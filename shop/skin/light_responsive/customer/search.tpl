{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, search.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
<div class="search">
    <form method="post" action="search.php" name="productsearchform">

      <input type="hidden" name="simple_search" value="Y" />
      <input type="hidden" name="mode" value="search" />
      <input type="hidden" name="posted_data[by_title]" value="Y" />
      <input type="hidden" name="posted_data[by_descr]" value="Y" />
      <input type="hidden" name="posted_data[by_sku]" value="Y" />
      <input type="hidden" name="posted_data[search_in_subcategories]" value="Y" />
      <input type="hidden" name="posted_data[including]" value="all" />

      {strip}
        <input type="text" name="posted_data[substring]" class="text" {if $search_prefilled.substring}value{else}placeholder{/if}="{$search_prefilled.substring|default:$lng.lbl_enter_keyword|escape}" />
        <button class="search-button" type="submit">
          <span>{$lng.lbl_search}</span>
        </button>
      {/strip}

    </form>
</div>
