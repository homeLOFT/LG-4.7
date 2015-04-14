{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, image_property.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{if $image and $image.image_type ne '' and $image.image_size gt 0}
  {$lng.lbl_image_size|escape}: {$image.image_x}x{$image.image_y}, {byte_format value=$image.image_size format=k}Kb
  {$lng.lbl_image_type|escape}: {$image.image_type|replace:"image/":""}
{/if}
