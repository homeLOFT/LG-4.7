{*
e6674b65a0ca61050cccdba7c735e98bdf39ac44, v3 (xcart_4_5_5), 2013-02-11 10:26:51, product_tip.tpl, aim 
vim: set ts=2 sw=2 sts=2 et:
*}

<{$wrapper_tag|default:"span"} id="{$id}_tooltip" style="display:none;">
  {$text}
</{$wrapper_tag|default:"span"}>

{capture name=tooltip assign=tt}
$(document).ready(function(){ldelim}
  $('#{$id}').cluetip({ldelim}
    clickThrough: true,
    local:true, 
    positionBy: 'mouse',
    hideLocal: false,
    showTitle: {if $show_title}true{else}false{/if},
    cluezIndex: {$cz_index|default:1100},
    tracking: true,
    {if $width gt 0}width: {$width}, {/if}
    clueTipClass: 'simple-products-tip'
  {rdelim});
{rdelim});
{/capture}
{load_defer file="tooltip`$id`" direct_info=$tt type="js"}
