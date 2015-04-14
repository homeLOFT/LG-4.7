{*
vim: set ts=2 sw=2 sts=2 et:
*}
{if $banner_tools_data}
<div class="banner-tools">
  {foreach from=$banner_tools_data item=item}
  <div class="banner-tools-box">
    {include file="`$item.template`"}
  </div>
  {/foreach}
</div>
{/if}
