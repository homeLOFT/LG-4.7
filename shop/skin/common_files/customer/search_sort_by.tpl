{*
5cc9610298ef43b7657ea422205e6381a46430d4, v3 (xcart_4_5_2), 2012-07-06 10:44:16, search_sort_by.tpl, tito
vim: set ts=2 sw=2 sts=2 et:
*}
{if $sort_fields and ($url or $navigation_script)}

  {if $url eq '' and $navigation_script ne ''}
    {assign var="url" value=$navigation_script}
  {/if}

  {if $navigation_page gt 1}
    {assign var="url" value=$url|amp|cat:"&amp;page=`$navigation_page`"}
  {else}
    {assign var="url" value=$url|amp}
  {/if}

  <div class="search-sort-bar no-print">
  {if $active_modules.Advanced_Customer_Reviews && $sort_links}

    {include file="modules/Advanced_Customer_Reviews/acr_search_sort_by.tpl"}

  {else}

    <strong class="search-sort-title">{$lng.lbl_sort_by}:</strong>

    {foreach from=$sort_fields key=name item=field}

      <span class="search-sort-cell">
        {if $name eq $selected}
          <a href="{$url}&amp;sort={$name|amp}&amp;sort_direction={if $direction eq 1}0{else}1{/if}" title="{$lng.lbl_sort_by|escape}: {$field|escape}" class="search-sort-link {if $direction}down-direction{else}up-direction{/if}">{$field|escape}</a>
        {else}
          <a href="{$url}&amp;sort={$name|amp}&amp;sort_direction={$direction}" title="{$lng.lbl_sort_by|escape}: {$field|escape}" class="search-sort-link">{$field|escape}</a>
        {/if}
      </span>

    {/foreach}

  {/if}

  </div>

{/if}
