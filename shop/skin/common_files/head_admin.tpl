{*
53fdea541b21df714b8038f3ccaa2112871ddc6d, v7 (xcart_4_6_5), 2014-09-02 12:29:56, head_admin.tpl, mixon
vim: set ts=2 sw=2 sts=2 et:
*}
{if $login ne ""}
{include file="quick_search.tpl"}
{/if}

<div id="head-admin">

  <div id="logo-gray">
    <a href="{$current_area}/home.php"><img src="{$ImagesDir}/logo_gray.png" alt="" /></a>
  </div>

  {if $login}

    {getvar var='top_news' func='func_tpl_get_admin_top_news'}
    <div class="admin-top-news">
      {$top_news.description|default:$top_news.title}
    </div>

    <div id="admin-top-menu">
        <ul>
        {include file="admin/top_menu.tpl"}
        </ul>
    </div>

  {/if}

  <div class="clearing"></div>

  {if $login and $menu}
    {include file="`$menu`/menu_box.tpl"}
  {/if}

</div>
