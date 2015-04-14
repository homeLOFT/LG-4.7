{* 
Id: home_page_banner.tpl,v 1.0.0.0 2012/09/11 16:52:52 joliaj Exp $
vim: set ts=2 sw=2 sts=2 et:
*}
{if $active_modules.Banner_System and $top_banners ne ''}
  {include file="modules/Banner_System/banner_rotator.tpl" banners=$top_banners banner_location='T'}
{elseif $active_modules.Demo_Mode and $active_modules.Banner_System}
  {include file="modules/Demo_Mode/banners.tpl"}
{/if}
