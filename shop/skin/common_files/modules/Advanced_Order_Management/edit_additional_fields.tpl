{*
24f90191c073209f108181dc930493dd0f0a6943, v2 (xcart_4_7_0), 2015-02-25 11:21:35, edit_additional_fields.tpl, mixon

vim: set ts=2 sw=2 sts=2 et:
*}
{* Define value filter *}
{if $fields_filter ne ''}
  {assign var='value_filter' value="[{$fields_filter}]"}
{else}
  {assign var='value_filter' value=""}
{/if}
{* Main loop *}
{foreach from=$additional_fields item=v key=k}
  {* Section filter *}
  {if $v.section eq $fields_section}
    {* Prepare value filter *}
    {if $value_filter ne ''}
      {assign var='field_value' value=$v.value.{$fields_filter}|escape}
      {assign var='field_original_value' value=$v.original_value.{$fields_filter}|escape}
    {else}
      {assign var='field_value' value=$v.value|escape}
      {assign var='field_original_value' value=$v.original_value|escape}
    {/if}
    {* Field flags *}
    {assign var='field_is_found' value=$v.is_found|escape}
    {assign var='field_is_avail' value=$v.is_avail|escape}
    {* Fields state css constructor *}
    {assign var='field_status_attr' value=""}
    {if $field_is_avail eq ''}
      {assign var='field_status_attr' value="data-aom-field-not-avail"}
    {/if}
    {if $field_is_found eq ''}
      {assign var='field_status_attr' value="data-aom-field-not-found"}
    {/if}
    {* Fields row and columns *}
    <tr{cycle name=$cycle_name values=', class="TableSubHead"'} {$field_status_attr}>
      <td>
        {$v.title}
        <input type="hidden" name="additional_fields[{$k}][fieldid]" value="{$v.fieldid|escape}" />
        <input type="hidden" name="additional_fields[{$k}][type]" value="{$v.type|escape}" />
        <input type="hidden" name="additional_fields[{$k}][section]" value="{$v.section|escape}" />
        <input type="hidden" name="additional_fields[{$k}][title]" value="{$v.title|escape}" />
        <input type="hidden" name="additional_fields[{$k}][avail]" value="{$v.avail|escape}" />
        {* Keep original values *}
        <input type="hidden" name="additional_fields[{$k}][is_found]" value="{$field_is_found}" />
        <input type="hidden" name="additional_fields[{$k}][is_avail]" value="{$field_is_avail}" />
        <input type="hidden" name="additional_fields[{$k}][original_value]{$value_filter}" value="{$field_original_value}" />
        {if $v.variants ne ''}
          {foreach from=$v.variants item=o key=i}
        <input type="hidden" name="additional_fields[{$k}][variants][{$i}]" value="{$o|escape}" />
          {/foreach}
        {/if}
      </td>
      <td>
      {if $v.type eq 'T'}
        <input type="text" name="additional_fields[{$k}][value]{$value_filter}" value="{$field_value}" {include file="main/attr_orig_data.tpl" data_orig_value=$field_original_value data_orig_keep_empty=$data_orig_keep_empty} />
      {elseif $v.type eq 'C'}
        <input type="checkbox" name="additional_fields[{$k}][value]{$value_filter}" value="Y" {if $field_value eq 'Y'} checked="checked"{/if} {include file="main/attr_orig_data.tpl" data_orig_value=($field_original_value eq 'Y') data_orig_keep_empty=$data_orig_keep_empty} />
      {elseif $v.type eq 'S'}
        <select name="additional_fields[{$k}][value]{$value_filter}" {include file="main/attr_orig_data.tpl" data_orig_value=$field_original_value data_orig_keep_empty=$data_orig_keep_empty}>
          {foreach from=$v.variants item=o}
          <option value="{$o|escape}"{if $field_value eq $o} selected="selected"{/if}>{$o|escape}</option>
          {/foreach}
        </select>
      {/if}
      </td>
      <td>
      {if $v.type eq 'C'}
        {if $field_original_value eq 'Y'}{$lng.lbl_aom_checked}{/if}
      {else}
        {$field_original_value}
      {/if}
      </td>
    </tr>
  {/if}
{/foreach}
