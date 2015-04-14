{*
c4876f0dc3c1be77d432d7277a473c36b8ae058c, v1 (xcart_4_6_1), 2013-09-07 11:40:24, placement_types.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{capture name=bml_placementtype}{strip}
{if $bml_page eq 'home'}
728x90
{elseif $bml_page eq 'category'}
728x90
{elseif $bml_page eq 'product'}
  {if $bml_location eq 'top'}
728x90
  {else}
234x60
  {/if}
{elseif $bml_page eq 'cart'}
  {if $bml_location eq 'top'}
800x66
  {else}
728x90
  {/if}
{/if}
{/strip}{/capture}
