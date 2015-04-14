{*
d19580b5f5f5d901a1315c71b3a6eada0239c273, v6 (xcart_4_6_4), 2014-04-07 17:45:38, order_message.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}

<h1>{$lng.lbl_invoice}</h1>

{if $this_is_printable_version eq ""}

  {capture name=dialog}

    <p class="text-block">{$lng.txt_order_placed}</p>
    {$lng.txt_order_placed_msg}

  {/capture}
  {include file="customer/dialog.tpl" title=$lng.lbl_confirmation content=$smarty.capture.dialog}

{/if}

{capture name=dialog}

  {if $this_is_printable_version eq ""}

    <div class="button-row-right">
      {if $orders[0].order.status eq 'A' or $orders[0].order.status eq 'P' or $orders[0].order.status eq 'C'}
        {assign var=bn_title value=$lng.lbl_print_receipt}
      {else}
        {assign var=bn_title value=$lng.lbl_print_invoice}
      {/if}

      {assign var=access_key value=""}
      {if $orders[0].order.access_key}
        {assign var=access_key value="&amp;access_key=`$orders[0].order.access_key`"}
      {/if}
      {include file="customer/buttons/button.tpl" button_title=$bn_title href="order.php?mode=invoice&orderid=`$orderids``$access_key`" target="preview_invoice" style="link"}
    </div>

    {if $active_modules.POS_System ne ""}
      {include file="modules/POS_System/receipt/customer_print_receipt_link.tpl"}
    {/if}

    <hr />

  {/if}

  {foreach from=$orders item=order}
    {include file="mail/html/order_invoice.tpl" is_nomail='Y' products=$order.products giftcerts=$order.giftcerts userinfo=$order.userinfo order=$order.order}
    <br />
    <br />
    <br />
    <br />

    {if $active_modules.Interneka}
      {include file="modules/Interneka/interneka_tags.tpl"} 
    {/if}

  {/foreach}

  {if $this_is_printable_version eq ""}

    <div class="buttons-row center">
      <div class="halign-center">
        {include file="customer/buttons/button.tpl" button_title=$lng.lbl_continue_shopping href="home.php" additional_button_class="main-button"}

        {if $active_modules.XAuth}
          {include file="modules/XAuth/rpx_ss_invoice.tpl"}
        {/if}

      </div>
    </div>

  {/if}

{/capture}
{include file="customer/dialog.tpl" title=$lng.lbl_invoice content=$smarty.capture.dialog noborder=true}
