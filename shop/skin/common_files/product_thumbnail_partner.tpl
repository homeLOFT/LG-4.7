{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, product_thumbnail_partner.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
<img src="{$http_location}/image.php?id={$productid}&amp;type=T"{if $image_x ne 0} width="{$image_x}"{/if}{if $image_y ne 0} height="{$image_y}"{/if} alt="{$product|escape}" />
