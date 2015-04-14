{*
15dff1d9a7b5f98401faa92dd7c427352bb62abb, v2 (xcart_4_4_0), 2010-08-04 17:13:37, menu_affiliate.tpl, igoryan
vim: set ts=2 sw=2 sts=2 et:
*}
{capture name=menu}
  <ul>
    <!-- begin cut here -->
    <li><a href="{if $config.Security.use_https_login eq 'Y'}{$catalogs_secure.partner}{else}{$catalogs.partner}{/if}/register.php">{$lng.lbl_partner_click_to_register}</a></li>
    <!-- end cut here -->
  </ul>
{/capture}
{include file="customer/menu_dialog.tpl" title=$lng.lbl_partner_affiliates content=$smarty.capture.menu additional_class="menu-affiliate"}
