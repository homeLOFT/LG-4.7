{*
a8c4ea097cadf2fa77455f334d8f791f06af6dd3, v6 (xcart_4_7_0), 2015-01-09 17:48:12, top_links.tpl, aim 

vim: set ts=2 sw=2 sts=2 et:
*}
<div id="top-links" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
  <ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
  {foreach from=$tabs item=tab key=ind}
    {inc value=$ind assign="ti"}
    <li class="ui-state-default ui-corner-top{if $tab.selected} ui-tabs-active ui-state-active{/if}">
      <a href="{if $tab.url}{$tab.url|amp}{else}#{$prefix}{$ti}{/if}" class="ui-tabs-anchor">{$tab.title|wm_remove|escape}</a>
    </li>
  {/foreach}
  </ul>
  <div class="ui-tabs-panel ui-widget-content"></div>
</div>
