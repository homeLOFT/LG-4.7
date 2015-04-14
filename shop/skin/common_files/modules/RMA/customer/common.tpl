{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, common.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{if $mode eq 'modify'}
  {include file="modules/RMA/customer/modify_return.tpl"}

{elseif $mode eq 'search'}
  {if $returns eq ''}
    {include file="modules/RMA/customer/search.tpl"}
  {/if}
  {include file="modules/RMA/customer/returns.tpl"}

{else}

  {include file="modules/RMA/customer/search.tpl"}

{/if}
