{*
c0b7a742f56d4cd9d5875fb9a121be48c4674208, v4 (xcart_4_7_0), 2014-12-20 17:15:41, location.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{if $category_location and $cat ne ""}
<div class="navigation-path">
{strip}
{section name=position loop=$category_location}
  {if $category_location[position].1 ne ''}
    {if $smarty.section.position.last}
      <span class="current">
    {else}
      <a href="{$category_location[position].1|amp}">
    {/if}
  {/if}
  {$category_location[position].0}
  {if $category_location[position].1 ne ''}
    {if $smarty.section.position.last}</span>{else}</a>{/if}
  {/if}
  {if $smarty.section.position.last ne "true"}&nbsp;/&nbsp;{/if}
{/section}
</div>
{/strip}
<br /><br />
{/if}
