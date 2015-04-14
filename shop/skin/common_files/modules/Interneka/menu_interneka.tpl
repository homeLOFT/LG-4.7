{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, menu_interneka.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{capture name=menu}
  <ul>
    <!-- begin cut here -->
    <li><a href="http://interneka.com/affiliate/AffiliateSignup.php?WID={$interneka_id6}">{$lng.lbl_interneka_click_to_register}</a></li>
    <!-- end cut here -->
  </ul>
{/capture}
{include file="customer/menu_dialog.tpl" title=$lng.lbl_interneka_affiliates content=$smarty.capture.menu additional_class="menu-interneka"}
