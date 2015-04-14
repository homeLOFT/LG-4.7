{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, table_head_cell.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{strip}
{if not $sort_field}
{assign var="sort_field" value=$search_prefilled.sort_field}
{/if}

{if not $sort_direction}
{assign var="sort_direction" value=$search_prefilled.sort_direction}
{/if}

{if $sort_field eq $column}

<img class="{if $sort_direction eq 1}img-down-direction{else}img-up-direction{/if}" src="{$ImagesDir}/spacer.gif" alt="" />
<a href="{$url}&amp;sort={$column}&amp;sort_direction={if $sort_direction eq 1}0{else}1{/if}">{$title}</a>

{else}

<a href="{$url}&amp;sort={$column}">{$title}</a>

{/if}
{/strip}
