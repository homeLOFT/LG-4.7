{*
07cfdd5cd6b48931504f1dee8c7a666b81c14d32, v3 (xcart_4_4_4), 2011-07-14 13:30:59, popup_magnifier.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
<script type="text/javascript" src="{$SkinDir}/modules/Magnifier/popup.js"></script>
<div class="magnifier-popup-link">
  <a href="popup_magnifier.php?productid={$product.productid}" onclick="javascript: winMagnifier = popup_magnifier('{$product.productid}',{$config.Magnifier.magnifier_width}+40,{$config.Magnifier.magnifier_height}+50); return false;" target="_blank">{$lng.lbl_click_to_zoom}</a>
</div>
