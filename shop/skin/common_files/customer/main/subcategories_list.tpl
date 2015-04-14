{*
16fd72c950254a8d4861815dac0d6147af93e43f, v3 (xcart_4_7_0), 2014-12-23 10:13:26, subcategories_list.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
<table cellspacing="0" summary="{$lng.txt_list_of_subcategories|escape}">

  <tr>
    <td>
      <ul class="subcategories">

        {foreach from=$categories item=subcat name=subcategories}
          <li{interline name=subcategories foreach_iteration="`$smarty.foreach.subcategories.iteration`" foreach_total="`$smarty.foreach.subcategories.total`"}>
            <a href="home.php?cat={$subcat.categoryid}">{$subcat.category|escape}</a>
            {if $config.Appearance.count_products eq "Y"}
              {if $subcat.product_count}
                ({$subcat.product_count} {$lng.lbl_products})
              {elseif $subcat.subcategory_count}
                ({$lng.lbl_N_categories|substitute:count:$subcat.subcategory_count})
              {/if}
            {/if}
          </li>
        {/foreach}

      </ul>
    </td>
  </tr>

</table>
