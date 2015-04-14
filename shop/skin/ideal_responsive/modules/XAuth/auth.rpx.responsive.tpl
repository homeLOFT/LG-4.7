{*
c864285ca2f65a336bddfc6bebad1cb9be317ec4, v2 (UNKNOWN_TAG), 2014-01-27 13:43:54, auth.rpx.responsive.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{include file="modules/XAuth/janrain_init.tpl"}
{if $layout eq 'vertical'}
  {include file="modules/XAuth/auth.rpx.vertical.tpl"}
{else}
  {include file="modules/XAuth/auth.rpx.horizontal.tpl"}
{/if}
