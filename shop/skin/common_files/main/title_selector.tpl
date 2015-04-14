{*
24f90191c073209f108181dc930493dd0f0a6943, v3 (xcart_4_7_0), 2015-02-25 11:21:35, title_selector.tpl, mixon

vim: set ts=2 sw=2 sts=2 et:
*}
<select name="{$name|default:"title"}" id="{$id|default:"title"}" {include file="main/attr_orig_data.tpl" data_orig_type=$data_orig_type data_orig_value=$data_orig_value data_orig_keep_empty=$data_orig_keep_empty}>
{if $titles}
{foreach from=$titles item=v}
  <option value="{if $use_title_id eq "Y"}{$v.titleid}{else}{$v.title_orig|escape}{/if}"{if $val eq $v.titleid} selected="selected"{/if}>{$v.title}</option>
{/foreach}
{else}
  <option value="{if $use_title_id eq "Y"}{$val}{/if}" selected="selected">{$lng.txt_no_titles_defined}</option>
{/if}
</select>
