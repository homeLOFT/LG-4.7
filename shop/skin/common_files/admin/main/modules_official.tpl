{*
7ca20ac64e17c762b84ce0380e3656819ee54adf, v2 (xcart_4_6_0), 2013-05-07 14:45:49, modules_official.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{include file="admin/main/modules_tags.tpl" modules_filter_tags=$paid_modules_filter_tags tag_type='extensions'}
<ul class="modules-list extensions">
{foreach from=$paid_modules item=m key=k}
<li id="li_extensions_{$k}" {if $m.tags} class="{foreach from=$m.tags item=tag} {$tag}{/foreach}"{/if}>
  <div class="module-icon">
    <a href="{$m.page}"><img src="{$m.icon}" /></a>
    <div class="module-price">{if $m.price_suffix}{$lng.lbl_modules_price_from} {/if}<span class="price">{if $m.price ne 0}${$m.price} {$m.price_suffix}{else}{$lng.lbl_modules_free_price}{/if}</span></div>
  </div>
  <div class="module-description">
    <div class="module-title"><a href="{$m.page}">{$m.name}</a></div>
    {$m.desc}
    <div class="module-details"><a href="{$m.page}">{$lng.lbl_read_more}</a></div>
  </div>
  <div class="clearing"></div>
</li>
{/foreach}
</ul>
