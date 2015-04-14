{*
7ca20ac64e17c762b84ce0380e3656819ee54adf, v1 (xcart_4_6_0), 2013-05-07 14:45:49, modules_tags.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{if $modules_filter_tags}
{load_defer file="admin/js/module_tags.js" type="js"}
{$lng.lbl_modules_filter_by_tag}
<div class="modules-tags">
{foreach from=$modules_filter_tags item=tag key=tag_key}
  <div class="modules-tag">
    <input type="radio" name="selectedtags_{$tag_type}" id="tag_{$tag_type}_{$tag_key}" onclick="javascript: toggleTag('{$tag_key}','{$tag_type}');"{if $tag.checked} checked="checked"{/if} /><label for="tag_{$tag_type}_{$tag_key}">{$tag.label} <span class="tag-count">{$tag.count}</span></label>
  </div>
{/foreach}
</div>
{/if}
