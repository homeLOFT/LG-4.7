{*
24f90191c073209f108181dc930493dd0f0a6943, v7 (xcart_4_7_0), 2015-02-25 11:21:35, zipcode.tpl, mixon

vim: set ts=2 sw=2 sts=2 et:
*}
{if not $static}

  {assign var=cntid value=$id|regex_replace:'/zipcode/':'country'|escape}
  <input type="text" id="{$id|escape}" class="zipcode{if $zip_section} {$zip_section}{/if}" name="{$name|escape}" size="32" maxlength="32" value="{$val|escape}" {include file="main/attr_orig_data.tpl" data_orig_type=$data_orig_type data_orig_value=$data_orig_zipcode data_orig_keep_empty=$data_orig_keep_empty} />
  {if $config.General.zip4_support eq 'Y' and not $nozip4}
  {strip}
    {assign var=zip4id value=$id|regex_replace:'/zipcode/':'zip4'|escape}
    {assign var=zip4name value=$name|regex_replace:'/zipcode/':'zip4'|escape}
    <span id="{$zip4id}_container">
      &nbsp;-&nbsp;
      <input type="text" id="{$zip4id}" class="zip4" name="{$zip4name}" size="10" maxlength="4" value="{$zip4|escape}" {include file="main/attr_orig_data.tpl" data_orig_type=$data_orig_type data_orig_value=$data_orig_zip4 data_orig_keep_empty=$data_orig_keep_empty} />
    </span>
  {/strip}
  {/if}

{else}
{if $is_csv_export eq 'Y'}
{$val}{if $zip4 ne ''}-{$zip4}{/if}
{else}
{$val|escape:"html"}{if $zip4 ne ''}-{$zip4|escape:"html"}{/if}
{/if}
{/if}
