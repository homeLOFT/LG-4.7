{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, auth.rpx.responsive.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{include file="modules/XAuth/janrain_init.tpl"}
{if $layout eq 'vertical'}
  {include file="modules/XAuth/auth.rpx.vertical.tpl"}
{else}
  {include file="modules/XAuth/auth.rpx.horizontal.tpl"}
{/if}
