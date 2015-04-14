{*
7a9fd04c70258a4ccf4fc5402bd968afa8e9afff, v3 (xcart_4_5_3), 2012-08-06 12:19:01, product_images.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{if $images ne ""}

  {capture name=dialog}

    {foreach from=$images item=i name=images}
      <div class="dpimage-container">
        <img src="{if $i.image_url}{$i.image_url|amp}{else}{$xcart_web_dir}/image.php?id={$i.imageid}&amp;type=D{/if}" alt="{$i.alt|escape}" title="{$i.alt|escape}"{if $smarty.foreach.images.last} class="last"{/if} />
      </div>
    {/foreach}

  {/capture}
  {if $nodialog}
    <div class="dpimages-list">
      {$smarty.capture.dialog}
    </div>
  {else}
    {include file="customer/dialog.tpl" title=$lng.lbl_detailed_images content=$smarty.capture.dialog additional_class="dpimages-list"}
  {/if}

{/if}
