{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, search_sort_by.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{if $url eq '' and $navigation_script ne ''}{assign var="url" value=$navigation_script|replace:"&":"&amp;"|cat:"&amp;"}{elseif $url ne ''}{assign var="url" value=$url|amp}{/if}
<table cellspacing="0" cellpadding="0">
<tr>
  <td class="SearchSortTitle">{$lng.lbl_sort_by}:</td>
{foreach from=$sort_fields key=name item=field}
{if $navigation_page gt 1}
{assign var="cur_url" value=$url|cat:"page="|cat:$navigation_page|cat:"&amp;sort="|cat:$name|cat:"&amp;sort_direction="}
{else}
{assign var="cur_url" value=$url|cat:"sort="|cat:$name|cat:"&amp;sort_direction="}
{/if}
  {if $name eq $selected}
  <td><a class="SearchSortLink" href="{$cur_url}{if $direction eq 1}0{else}1{/if}" title="{$lng.lbl_sort_by|escape}: {$field}"><img src="{$ImagesDir}/{if $direction}darrow.gif{else}uarrow.gif{/if}" class="SearchSortImg" alt="{$lng.lbl_sort_direction|escape}" /></a></td>
  {/if}
  <td class="SearchSortCell"><a class="SearchSortLink" href="{$cur_url}{if $name eq $selected}{if $direction eq 1}0{else}1{/if}{else}{$direction}{/if}" title="{$lng.lbl_sort_by|escape}: {$field}">{if $name eq $selected}<b>{/if}{$field}{if $name eq $selected}</b>{/if}</a></td>
{/foreach}
</tr>
</table>
