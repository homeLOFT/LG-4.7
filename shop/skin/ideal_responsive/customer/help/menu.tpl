{*
8ec8b858917c921a726e2197071ce74d020e5ead, v1 (xcart_4_6_0), 2013-04-02 16:43:59, menu.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}

{if $static_pages_list_style eq "menu"}

{capture name=menu}

  <ul>
    <li><a href="help.php">{$lng.lbl_help_zone}</a></li>
    <li><a href="help.php?section=contactus&amp;mode=update">{$lng.lbl_contact_us}</a></li>
    {foreach from=$pages_menu item=p}
      {if $p.show_in_menu eq 'Y'}
        <li><a href="pages.php?pageid={$p.pageid}">{$p.title|amp}</a></li>
      {/if}
    {/foreach}
  </ul>

{/capture}
{include file="customer/menu_dialog.tpl" title=$lng.lbl_need_help content=$smarty.capture.menu additional_class="menu-help"}

{else}

<a href="help.php">{$lng.lbl_help_zone}</a>
<a href="help.php?section=contactus&amp;mode=update">{$lng.lbl_contact_us}</a>
{foreach from=$pages_menu item=p name=static_pages_list}
  {if $smarty.foreach.static_pages_list.iteration le 3}
    {if $p.show_in_menu eq 'Y'}
    	<a href="pages.php?pageid={$p.pageid}">{$p.title|amp}</a>
    {/if}
  {/if}
{/foreach}

{/if}

