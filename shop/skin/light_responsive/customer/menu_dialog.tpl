{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, menu_dialog.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
<div class="menu-dialog{if $additional_class} {$additional_class}{/if}">
  <div class="title-bar {if $link_href} link-title{/if}">
    {strip}

      {if $minicart}
        <span class="icon ajax-minicart-icon fa fa-shopping-cart fa-lg"></span>
      {else}
        {if $link_href}
	      <h2><a href="{$link_href}">{$title}</a></h2>
        {else}
	      <h2>{$title}</h2>
        {/if}
      {/if}

    {/strip}
  </div>
  <div class="content">
    {$content}
  </div>
  {if $minicart}
	<div class="clearing"></div>
  {/if}
</div>
