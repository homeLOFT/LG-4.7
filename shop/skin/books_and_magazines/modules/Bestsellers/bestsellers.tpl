{*
a11d55b2c22f3ed2548072e03cca2ab6454c2e76, v2 (xcart_4_6_0), 2013-04-08 13:50:24, bestsellers.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{getvar var=bestsellers func=func_tpl_get_bestsellers}
{if $bestsellers}

  {capture name=bestsellers}

    {include file="customer/simple_products_list.tpl" products=$bestsellers class=""}

  {/capture}
  {include file="customer/dialog.tpl" title=$lng.lbl_bestsellers content=$smarty.capture.bestsellers}

{/if}
