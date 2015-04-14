{*
59b18741a7e0c882b9e5cd007ec33ae63ba56ab6, v1 (xcart_4_5_0), 2012-04-05 11:53:47, menu_dialog.tpl, ferz
vim: set ts=2 sw=2 sts=2 et:
*}
<div class="menu-dialog{if $additional_class} {$additional_class}{/if}">
  <div class="title-bar {if $link_href} link-title{/if}">
    {strip}

      {**if $link_href}
        <span class="title-link">
          <a href="{$link_href}" class="title-link"><img src="{$ImagesDir}/spacer.gif" alt=""  /></a>
        </span>
      {/if}

      <img class="icon ajax-minicart-icon" src="{$ImagesDir}/spacer.gif" alt="" />***}
      <h2>{$title}</h2>

    {/strip}
  </div>
  <div class="content">
    {$content}
  </div>
</div>
