{*
9c264c18edbc4a5a085867039f09ec43238cd0ca, v1 (xcart_4_6_2), 2013-11-02 09:05:57, auth.rpx.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{if $config.XAuth.xauth_rpx_display_mode eq 'v'}
  {include file="modules/XAuth/auth.rpx.responsive.tpl" layout='vertical'}
{else}
  {include file="modules/XAuth/auth.rpx.responsive.tpl" layout='horizontal'}
{/if}
