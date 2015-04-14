{*
649af528661ff62b390c59d29308411591eddc7d, v9 (xcart_4_6_3), 2014-02-28 14:37:06, modules_installed.tpl, mixon
vim: set ts=2 sw=2 sts=2 et:
*}
{include file="admin/main/modules_tags.tpl" modules_filter_tags=$modules_filter_tags tag_type='modules'}
<form action="modules.php?mode=update" method="post" name="modulesform">
{if !$admin_safe_mode}
{load_defer file="admin/js/toggle_modules.js" type="js"}
{/if}
<ul class="modules-list" id="modules-list">
{foreach from=$modules item=m}
<li id="li_modules_{$m.module_name}" class="{if $m.active eq 'Y'}active{/if}{foreach from=$m.tags item=tag} {$tag}{/foreach}">
<div class="module-settings">
  <div class="module-enable">
  {assign var="module_requirements" value="module_requirements_`$m.module_name`"}
  <input type="checkbox" id="{$m.module_name}" name="{$m.module_name}"{if $m.active eq "Y"} checked="checked"{/if}{if not $m.requirements_passed or $admin_safe_mode} disabled="disabled"{/if}{if !$admin_safe_mode} onclick="javascript: toggleModule('{$m.module_name}');"{/if} />
  <label for="{$m.module_name}">{$lng.lbl_enable}</label>
  </div>
  {if $m.options_url ne ""}
  <div class="module-configure">
  {include file="buttons/button.tpl" button_title=$lng.lbl_configure href=$m.options_url|amp substyle="modules"}
  </div>
  {/if}
</div>
<div class="module-description">
{assign var="module_name" value="module_name_`$m.module_name`"}
<div class="module-title">{if $lng.$module_name}{$lng.$module_name}{else}{$m.module_name}{/if}</div>
{assign var="module_descr" value="module_descr_`$m.module_name`"}
{if $lng.$module_descr}{$lng.$module_descr}{else}{$m.module_descr}{/if}
{if !$m.requirements_passed}
<br />
<table cellpadding="2">
  <tr>
    <td><img src="{$ImagesDir}/icon_warning_small.gif" alt="" /></td>
    <td><font class="SmallText">{$lng.$module_requirements}</font>{if $m.active eq "Y"} <font class="AdminSmallMessage">{$lng.txt_disable_notconfigured_module}</font>{/if}</td>
  </tr>
</table>
{/if}
</div>
<div class="clearing"></div>
</li>
{/foreach}
</ul>
<noscript>
<br />
<div id="sticky_content">
  <div class="main-button">
    <input class="big-main-button" type="submit" value="{$lng.lbl_apply_changes|strip_tags:false|escape}" />
  </div>
</div>
</noscript>
</form>
