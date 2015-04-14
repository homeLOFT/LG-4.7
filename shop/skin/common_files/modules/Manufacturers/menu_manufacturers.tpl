{*
d672e73d94f39ed7c84f3122ca0af7a5654f9ecb, v3 (xcart_4_4_2), 2010-12-09 14:00:53, menu_manufacturers.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{if $manufacturers_menu ne ''}

  {capture name=menu}
    <ul>

      {foreach from=$manufacturers_menu item=m}
         <li><a href="manufacturers.php?manufacturerid={$m.manufacturerid}">{$m.manufacturer|amp}</a></li>
      {/foreach}

      {if $show_other_manufacturers}
        <li><a href="manufacturers.php">{$lng.lbl_other_manufacturers}</a></li>
      {/if}

    </ul>
  {/capture}
  {include file="customer/menu_dialog.tpl" title=$lng.lbl_manufacturers content=$smarty.capture.menu additional_class="menu-manufacturers"}

{/if}
