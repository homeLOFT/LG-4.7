{*
dd9ffbc62cf705749b8c54bbf2c006bc1bf66c01, v2 (xcart_4_5_3), 2012-08-16 12:05:38, menu.tpl, aim
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

