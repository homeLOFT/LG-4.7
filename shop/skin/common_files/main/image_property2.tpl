{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, image_property2.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{strip}
  {if $image and $image.image_type ne '' and $image.image_size gt 0}
    {$image.image_x}x{$image.image_y}, {byte_format value=$image.image_size format=k} kb
  {/if}
  {if $show_modified}
    &nbsp;&nbsp;<span style="color: #b51a00;"><b>{$lng.lbl_modified}</b></span>
  {/if}
{/strip}
