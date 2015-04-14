{*
2a30536f400a253c86be1a18eb00603b4ad45f18, v1 (xcart_4_5_1), 2012-06-22 11:52:57, menu_dialog.tpl, aim
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
	  {if $minicart}
		  <img class="icon ajax-minicart-icon" src="{$ImagesDir}/spacer.gif" alt="" />
	  {else}
	      <h2>{$title}</h2>
	  {/if}

    {/strip}
  </div>
  <div class="content">
    {$content}
  </div>
  {if $minicart}
	<div class="clearing"></div>
	<div class="t-l"></div><div class="t-r"></div>
	<div class="b-l"></div><div class="b-r"></div>
  {/if}
</div>
