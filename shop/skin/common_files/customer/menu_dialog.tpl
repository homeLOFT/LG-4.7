{*
cdb190de5a9a72bae7db0a91e06259a7f7a55f55, v2 (xcart_4_4_0), 2010-07-22 08:33:38, menu_dialog.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
<div class="menu-dialog{if $additional_class} {$additional_class}{/if}">
  <div class="title-bar {if $link_href} link-title{/if}">
    {strip}

      {if $link_href}
        <span class="title-link">
          <a href="{$link_href}" class="title-link"><img src="{$ImagesDir}/spacer.gif" alt=""  /></a>
        </span>
      {/if}

      <img class="icon ajax-minicart-icon" src="{$ImagesDir}/spacer.gif" alt="" />
      <h2>{$title}</h2>

    {/strip}
  </div>
  <div class="content">
    {$content}
  </div>
</div>
