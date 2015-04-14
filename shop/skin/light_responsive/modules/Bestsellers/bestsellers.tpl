{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, bestsellers.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{getvar var=bestsellers func=func_tpl_get_bestsellers}
{if $bestsellers}

  {capture name=bestsellers}

    {foreach from=$bestsellers key=k item=bestseller}
      {if !$bestsellers[$k].appearance}
        {$bestsellers[$k].appearance.has_price = ($bestseller.taxed_price > 0)}
      {/if}
    {/foreach}

    {include file="customer/main/products.tpl" products=$bestsellers module="bestsellers"}

  {/capture}
  {include file="customer/dialog.tpl" title=$lng.lbl_bestsellers content=$smarty.capture.bestsellers additional_class="products-dialog dialog-bestsellers"}

{/if}
