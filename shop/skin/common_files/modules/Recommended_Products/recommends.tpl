{*
f68245089cd399d3316aa6335b8d128a6a7103d0, v2 (xcart_4_4_1), 2010-08-16 08:38:26, recommends.tpl, igoryan
vim: set ts=2 sw=2 sts=2 et:
*}
{if $printable ne 'Y' and $recommends}

  {capture name=dialog}

    {include file="customer/simple_products_list.tpl" title=$lng.txt_recommends_comment products=$recommends class="rproducts"}

  {/capture}

  {if $nodialog}
    {$smarty.capture.dialog}
  {else}
    {include file="customer/dialog.tpl" content=$smarty.capture.dialog title=$lng.txt_recommends_comment}
  {/if}

{/if}
