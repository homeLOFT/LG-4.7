{*
16fd72c950254a8d4861815dac0d6147af93e43f, v3 (xcart_4_7_0), 2014-12-23 10:13:26, tabs.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{if $speed_bar}
  <div class="tabs{if $all_languages_cnt gt 1} with_languages{/if} monitor">
    <ul>
      {math equation="round(100/x,2)" x=$speed_bar|@count assign="cell_width"}
      {foreach from=$speed_bar item=sb name=tabs}
         {strip}
			<li{interline name=tabs foreach_iteration="`$smarty.foreach.tabs.iteration`" foreach_total="`$smarty.foreach.tabs.total`" additional_class="hidden-xs"}>
				<a href="{$sb.link|amp}">
					{$sb.title}
					<img src="{$ImagesDir}/spacer.gif" alt="" />
				</a>
				<div class="t-l"></div><div class="t-r"></div>
			</li>
			<li{interline name=tabs foreach_iteration="`$smarty.foreach.tabs.iteration`" foreach_total="`$smarty.foreach.tabs.total`" additional_class="visible-xs"} style="width: {$cell_width}%">
				<a href="{$sb.link|amp}">
					{$sb.title}
					<img src="{$ImagesDir}/spacer.gif" alt="" />
				</a>
				{if $smarty.foreach.tabs.last}<div class="mobile-tab-delim first"></div><div class="t-l first"></div>{else}<div class="t-l"></div>{/if}<div class="t-r"></div><div class="mobile-tab-delim"></div>
			</li>
		{/strip}
      {/foreach}

    </ul>
  </div>
{/if}
