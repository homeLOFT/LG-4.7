{*
e66af9def9eb9914ed35b2e42b34821337ba81e2, v20 (xcart_4_5_4), 2012-10-24 13:39:58, service_css.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{load_defer file="css/`$smarty.config.CSSFilePrefix`.css" type="css"}
{if $config.UA.browser eq "MSIE"}
  {assign var=ie_ver value=$config.UA.version|string_format:'%d'}
  {load_defer file="css/`$smarty.config.CSSFilePrefix`.IE`$ie_ver`.css" type="css"}
{/if}

{if $config.UA.browser eq 'Firefox' or $config.UA.browser eq 'Mozilla'}
  {load_defer file="css/`$smarty.config.CSSFilePrefix`.FF.css" type="css"}
{/if}

{if $config.UA.browser eq 'Opera'}
  {load_defer file="css/`$smarty.config.CSSFilePrefix`.Opera.css" type="css"}
{/if}

{if $config.UA.browser eq 'Chrome'}
  {load_defer file="css/`$smarty.config.CSSFilePrefix`.GC.css" type="css"}
{/if}

{load_defer file="lib/cluetip/jquery.cluetip.css" type="css"}

{if $main eq 'product'}
  {getvar var=det_images_widget}
  {if $det_images_widget eq 'cloudzoom'}
    {load_defer file="lib/cloud_zoom/cloud-zoom.css" type="css"}
  {elseif $det_images_widget eq 'colorbox'}
    {load_defer file="lib/colorbox/colorbox.css" type="css"}
  {/if}
{/if}

{getvar func='func_tpl_is_jcarousel_is_needed'}
{if $active_modules.Wishlist ne '' and $func_tpl_is_jcarousel_is_needed}
  {load_defer file="modules/Wishlist/main_carousel.css" type="css"}
{/if}

{include file='customer/service_css_modules.tpl'}

{if $AltSkinDir}
  {load_defer file="css/altskin.css" type="css"}
  {if $config.UA.browser eq "MSIE"}
    {load_defer file="css/altskin.IE`$ie_ver`.css" type="css"}
  {/if}

  {if $config.UA.browser eq 'Firefox' or $config.UA.browser eq 'Mozilla'}
  	{load_defer file="css/altskin.FF.css" type="css"}
  {/if}

  {include file='customer/service_css_modules.tpl' is_altskin=true}
{/if}

{if $custom_styles}
{load_defer file="css/custom_styles" direct_info=$custom_styles type="css"}
{/if}
