{*
69d72e582f8929884049f7374c96bc381ca90d4c, v2 (xcart_4_4_2), 2010-10-04 11:51:54, related_products.tpl, ferz
vim: set ts=2 sw=2 sts=2 et:
*}
{if $product_links ne "" and $printable ne "Y"}

  {capture name=dialog}

    {include file="customer/simple_products_list.tpl" title=$lng.lbl_related_products products=$product_links open_new_window=$config.Upselling_Products.upselling_new_window class="uproducts"}

  {/capture}

  {if $nodialog}
    {$smarty.capture.dialog}
  {else}
    {include file="customer/dialog.tpl" content=$smarty.capture.dialog title=$lng.lbl_related_products}
  {/if}

{/if}
