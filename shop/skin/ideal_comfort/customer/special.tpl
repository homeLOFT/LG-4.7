{*
d19580b5f5f5d901a1315c71b3a6eada0239c273, v6 (xcart_4_6_4), 2014-04-07 17:45:38, special.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{capture name=submenu}

  {if $active_modules.Manufacturers ne "" and $config.Manufacturers.manufacturers_menu ne "Y"}
    <li><a href="manufacturers.php">{$lng.lbl_manufacturers}</a></li>
  {/if}

  {if $active_modules.Gift_Registry ne ""}
    {include file="modules/Gift_Registry/giftreg_menu.tpl"}
  {/if}

  {if $active_modules.Feature_Comparison ne ""}
    {include file="modules/Feature_Comparison/customer_menu.tpl"}
  {/if}

  {if $active_modules.Survey ne ""}
    {include file="modules/Survey/menu_special.tpl"}
  {/if}

  {if $active_modules.Special_Offers ne ""}
    {include file="modules/Special_Offers/menu_special.tpl"}
  {/if}

  {if $active_modules.Sitemap ne ""}
    {include file="modules/Sitemap/menu_item.tpl"}
  {/if}

  {if $active_modules.Products_Map ne ""}
    {include file="modules/Products_Map/menu_item.tpl"}
  {/if}

  {if $active_modules.New_Arrivals ne ""}
    {include file="modules/New_Arrivals/new_arrivals_link.tpl"}
  {/if}

  {if $active_modules.On_Sale ne ""}
    {include file="modules/On_Sale/on_sale_link.tpl"}
  {/if}

  {if $active_modules.EU_Cookie_Law ne ""}
    {include file="modules/EU_Cookie_Law/menu_item_special.tpl"}
  {/if}

  {if $active_modules.POS_System ne ""}
    {include file="modules/POS_System/scan_barcode_link.tpl"}
  {/if}

{/capture}
{if $smarty.capture.submenu|trim}
  <a name="special"></a>
  {capture name=menu}
    <ul>
      {$smarty.capture.submenu|trim}
    </ul>
  {/capture}
  {include file="customer/menu_dialog.tpl" title=$lng.lbl_special content=$smarty.capture.menu additional_class="menu-special"}
{/if}
