{*
c4876f0dc3c1be77d432d7277a473c36b8ae058c, v1 (xcart_4_6_1), 2013-09-07 11:40:24, top_banner.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{if $main eq 'catalog' and $cat ne ''}
  {assign var='bml_page' value='category'}
{elseif $main eq 'catalog'}
  {assign var='bml_page' value='home'}
{elseif $main eq 'product'}
  {assign var='bml_page' value='product'}
{elseif $main eq 'cart'}
  {assign var='bml_page' value='cart'}
{/if}
{if
  $bml_page eq 'home' and $config.Bill_Me_Later.bml_banner_on_home eq 'top'
  or $bml_page eq 'category' and $config.Bill_Me_Later.bml_banner_on_category eq 'top'
  or $bml_page eq 'product' and $config.Bill_Me_Later.bml_banner_on_product eq 'top'
  or $bml_page eq 'cart' and $config.Bill_Me_Later.bml_banner_on_cart eq 'top'
}
  {include file="modules/Bill_Me_Later/banner.tpl" bml_page=$bml_page}
{/if}
