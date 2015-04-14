{*
6959437a96cdcd4bcfb47420a77ee0191d1b2842, v1 (xcart_4_6_2), 2013-09-20 11:26:08, product_admin_preview_top.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
<div id="head-admin">
  <div class="logo-gray">
    <img src="{$ImagesDir}/logo_gray.png" alt="" />
  </div>
  <div class="modify-link">
  <a id="product_modify_link" href="{if $is_admin_preview eq 'A'}{$catalogs.admin}{else}{$catalogs.provider}{/if}/product_modify.php?productid={$product.productid}">{$lng.lbl_modify_this_product}</a>
  </div>
  <div class="clearing"></div>
</div>
