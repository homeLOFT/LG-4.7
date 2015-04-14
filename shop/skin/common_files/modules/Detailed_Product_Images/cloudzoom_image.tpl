{*
0172fc09470c9be7a243e96a8d01b4cf4b7f4ab9, v8 (xcart_4_6_2), 2014-01-22 18:51:42, cloudzoom_image.tpl, mixon
vim: set ts=2 sw=2 sts=2 et:
*}
<div id="productImageBox" class="image-box">
{assign var="cloudzoom_general_popup_params" value="tint: '#fff', position: 'right', smoothMove: 3, adjustX: 10, adjustY: -4"}
{assign var="cloudzoom_default_image_popup_sizes" value="zoomWidth: `$images.0.cloudzoom_popup_width`, zoomHeight: `$images.0.cloudzoom_popup_height`"}
<a href="{$images.0.image_url}" class="cloud-zoom" id="cloud_zoom_image" rel="{$cloudzoom_general_popup_params}, {$cloudzoom_default_image_popup_sizes}">
{include file="product_thumbnail.tpl" productid=$images.0.id image_x=$images.0.thbn_image_x image_y=$images.0.thbn_image_y product=$product.product tmbn_url=$images.0.thbn_url id="product_thumbnail" type="D"}
</a>
</div>
{if $active_modules.Product_Options and $product.is_variants eq 'Y'}
<div id="variantImageBox" class="image-box" style="{if $max_image_width gt 0}width: {$max_image_width}px;{/if} {if $max_image_height gt 0}height: {$max_image_height}px;{/if}; display: none;">
  <img id="variantThumbnail" />
</div>
<script type="text/javascript">
//<![CDATA[
  var useSwitchImageBox = true;
//]]>
</script>
{capture name="switch_image_box"} switchImageBox("product");{/capture}
{/if}

{if $config.Detailed_Product_Images.det_image_icons_box eq 'Y'}
 
<div class="dpimages-icons-box">
  {foreach from=$images item=i name=images}
    {if $config.Detailed_Product_Images.det_image_icons_limit lte 0 or $config.Detailed_Product_Images.det_image_icons_limit > $smarty.foreach.images.index}
    {assign var="cloudzoom_selected_image_popup_params" value="`$cloudzoom_general_popup_params`, zoomWidth: `$i.cloudzoom_popup_width`, zoomHeight: `$i.cloudzoom_popup_height`"}
    <a href="{$i.image_url|amp}" class="cloud-zoom-gallery" id="dpicon_{$smarty.foreach.images.index}" rel="useZoom: 'cloud_zoom_image', smallImage: '{$i.thbn_url|escape:"javascript"}'" title="{$i.alt|escape}" onclick='javascript: cloudzoom_change_image_size({$i.thbn_image_x}, {$i.thbn_image_y}); cloudzoom_change_popup_params("{$cloudzoom_selected_image_popup_params|escape}");{$smarty.capture.switch_image_box}'><img src="{$i.icon_url|amp}" alt="{$i.alt|escape}" title="{$i.alt|escape}" width="{$i.icon_image_x}" height="{$i.icon_image_y}" /></a>
    {/if}
    {/foreach}
  <div class="clearing"></div>
</div>

{/if}
