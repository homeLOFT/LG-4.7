{*
fee4016d449e6270cb5a70a242fb004cc7e89d5b, v4 (xcart_4_4_0_beta_2), 2010-07-13 10:47:10, dialog.tpl, igoryan
vim: set ts=2 sw=2 sts=2 et:
*}
<div class="dialog{if $additional_class} {$additional_class}{/if}{if $noborder} noborder{/if}{if $sort and $printable ne 'Y'} list-dialog{/if}">
  {if not $noborder}
    <div class="title">
      <h2>{$title}</h2>
      {if $sort and $printable ne 'Y'}
        <div class="sort-box">
          {if $selected eq '' and $direction eq ''}
            {include file="customer/search_sort_by.tpl" selected=$search_prefilled.sort_field direction=$search_prefilled.sort_direction url=$products_sort_url}
          {else}
            {include file="customer/search_sort_by.tpl" url=$products_sort_url}
          {/if}
        </div>
      {/if}
    </div>
  {/if}
  <div class="content">{$content}</div>
</div>
