{*
16fd72c950254a8d4861815dac0d6147af93e43f, v7 (xcart_4_7_0), 2014-12-23 10:13:26, categories.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}

{if $categories_menu_list ne '' or $fancy_use_cache}
{capture name=menu}

{if $active_modules.Flyout_Menus}

  {include file="modules/Flyout_Menus/categories.tpl"}
  {assign var="additional_class" value="menu-fancy-categories-list"}

{else}

  <ul>
    {foreach from=$categories_menu_list item=c name=categories}
      <li{interline name=categories foreach_iteration="`$smarty.foreach.categories.iteration`" foreach_total="`$smarty.foreach.categories.total`"}><a href="home.php?cat={$c.categoryid}" title="{$c.category|escape}">{$c.category|amp}</a></li>
    {/foreach}
  </ul>

  {assign var="additional_class" value="menu-categories-list"}

{/if}

{/capture}
{include file="customer/menu_dialog.tpl" title=$lng.lbl_categories content=$smarty.capture.menu}
{/if}
