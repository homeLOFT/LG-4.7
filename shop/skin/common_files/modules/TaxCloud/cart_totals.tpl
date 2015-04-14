{*
e684c1191ca570441d299967ddc29e4bdb55d36f, v2 (xcart_4_6_2), 2014-01-25 04:13:25, cart_totals.tpl, mixon
vim: set ts=2 sw=2 sts=2 et:

TaxCloud module: Add/update/remove exemption certificate to cart
*}

{if $active_modules.TaxCloud && $login && !$cart.tax_cloud_is_not_used}

{if $cart.taxcloud_certificateID}

<div class="taxcloud-cert dcoupons-clear">
  <span id="xmptlink" class="navlink" title="{$lng.taxcloud_lbl_exemption_cert}">{$cart.taxcloud_certificateID}</span>
  <a href="cart.php?mode=checkout&amp;action=taxcloud_clear" class="unset-cert-link" title="{$lng.taxcloud_lbl_unset_certificate|escape}"><img src="{$ImagesDir}/spacer.gif" alt="{$lng.taxcloud_lbl_unset_certificate|escape}" /></a>
</div>

{else}

<div class="taxcloud-cert">
  <span id="xmptlink" class="navlink">{$lng.taxcloud_lbl_are_you_exempt}</span>
</div>

{/if}

<input type="hidden" id="taxcloud_exemption_certificate">

<hr />

{/if}
