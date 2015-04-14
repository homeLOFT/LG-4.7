{*
ca665af77f64f59a6fceb508a286bfed4e55f844, v5 (xcart_4_5_3), 2012-09-03 08:27:13, category_selector.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{load_defer file="js/category_selector.js" type="js"}
<div id='layer' style="display:none; position:absolute; background-color:#FFFBD3; border:1px solid #000000;left:0px;top:0px;z-index: 10;"></div>
<iframe  scrolling="no" frameborder="0" style="position:absolute;top:0px;left:0px;display:none;width:100px;height:14px;" id="iframe" src="{$ImagesDir}/spacer.gif"></iframe>
<select name="{$field|default:"categoryid"}"{$extra} onchange="javascript: showTitle(this.options[this.selectedIndex].text, 'right');"{if $size} size="{$size}"{/if}>
{if $display_empty eq 'P'}
  <option value="">{$lng.lbl_please_select_category}</option>
{elseif $display_empty eq 'E'}
  <option value="">&nbsp;</option>
{/if}
{foreach from=$allcategories item=c key=catid}
  <option value="{$catid}"{if $categoryid eq $catid} selected="selected"{/if}>{$c|amp}</option>
{/foreach}
</select>
