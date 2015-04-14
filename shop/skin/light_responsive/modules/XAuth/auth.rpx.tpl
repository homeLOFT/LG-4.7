{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, auth.rpx.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{if $config.XAuth.xauth_rpx_display_mode eq 'v'}
  {include file="modules/XAuth/auth.rpx.responsive.tpl" layout='vertical'}
{else}
  {include file="modules/XAuth/auth.rpx.responsive.tpl" layout='horizontal'}
{/if}
