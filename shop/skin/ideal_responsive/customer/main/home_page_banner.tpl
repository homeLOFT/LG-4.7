{*
8ec8b858917c921a726e2197071ce74d020e5ead, v1 (xcart_4_6_0), 2013-04-02 16:43:59, home_page_banner.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{if $active_modules.Banner_System and $top_banners ne ''}
  {include file="modules/Banner_System/banner_rotator.tpl" banners=$top_banners banner_location='T'}
{elseif $active_modules.Demo_Mode and $active_modules.Banner_System}
  {include file="modules/Demo_Mode/banners.tpl"}
{else}
  <div class="welcome-img">
    <img src="{$AltImagesDir}/custom/welcome_picture.jpg" alt="" title="" usemap="#xcart" />
    <map id="xcart" name="xcart">
      <area shape="rect" coords="336,33,457,230" href="http://www.x-cart.com" target="_blank" alt="X-Cart" />
    </map>
  </div>
{/if}
