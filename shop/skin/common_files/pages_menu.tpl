{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, pages_menu.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{section name=pg loop=$pages_menu}
  <a href="pages.php?pageid={$pages_menu[pg].pageid}" class="VertMenuItems">{$pages_menu[pg].title}</a><br />
{/section}
