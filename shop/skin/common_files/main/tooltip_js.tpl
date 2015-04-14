{*
e66af9def9eb9914ed35b2e42b34821337ba81e2, v10 (xcart_4_5_4), 2012-10-24 13:39:58, tooltip_js.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{if $type eq 'label'}

  <label for="{$idfor|default:"for_tooltip_link"}">
    <a class="{$class|default:"NeedHelpLink"|escape}"{if $show_title} title="{$title|default:$lng.lbl_need_help|escape}"{/if} id="{$id|default:"tooltip_link"}" {if $ajax_source}rel="{$ajax_source}"{else}rel="#{$id|default:"tooltip_link"}_tooltip"{/if}>{$title|default:$lng.lbl_need_help}</a>
  </label>

{elseif $type eq 'img'}

  <a href="javascript:void(0);" class="" id="{$id|default:"tooltip_link"}"{if $show_title} title="{$title|escape}"{/if} {if $ajax_source}rel="{$ajax_source}"{else}rel="#{$id|default:"tooltip_link"}_tooltip"{/if}>
    <img src="{$ImagesDir}/{$alt_image|default:"help_sign.gif"}" width="15" height="15" alt="{$title|default:$lng.lbl_need_help|escape}" />
  </a>

{else}

  <a class="{$class|default:"NeedHelpLink"|escape}"{if $show_title} title="{$title|default:$lng.lbl_need_help|escape}"{/if} id="{$id|default:"tooltip_link"}" href="#{$id|default:"tooltip_link"}_tooltip" {if $ajax_source}rel="{$ajax_source}"{else}rel="#{$id|default:"tooltip_link"}_tooltip"{/if}>{$title|default:$lng.lbl_need_help}</a>

{/if}

<{$wrapper_tag|default:"span"} id="{$id|default:"tooltip_link"}_tooltip" style="display:none;">
  {$text}
</{$wrapper_tag|default:"span"}>

{capture name=tooltip assign=tt}
$(document).ready(function(){ldelim}
  $('#{$id|default:"tooltip_link"}').cluetip({ldelim}
    local: {if $ajax_source}false{else}true{/if},
    hideLocal: false,
    showTitle: {if $show_title}true{else}false{/if},
    cluezIndex: {$cz_index|default:1100},
{if $width gt 0}
    width: {$width},
{/if}
{if $sticky}
      sticky: true,
      mouseOutClose: true,
      closePosition: 'bottom',
      closeText: '{$lng.lbl_close|wm_remove|escape:"javascript"}',
{/if}
    clueTipClass: '{$extra_class|default:"default"}'
  {rdelim});
{rdelim});
{/capture}
{load_defer file="tooltip`$id`" direct_info=$tt type="js"}
