{*
e3d566aa926319b6bc7ccc22ab9eacd31c55f836, v4 (xcart_4_4_0_beta_2), 2010-06-29 14:20:06, section.tpl, igoryan 
vim: set ts=2 sw=2 sts=2 et:
*}
{if not ($smarty.cookies.robot eq 'X-Cart Catalog Generator' and $smarty.cookies.is_robot eq 'Y')}
  {include file="modules/Recently_Viewed/content.tpl"}
{/if}
