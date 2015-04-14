{*
24f90191c073209f108181dc930493dd0f0a6943, v2 (xcart_4_7_0), 2015-02-25 11:21:35, attr_orig_data.tpl, mixon

vim: set ts=2 sw=2 sts=2 et:
*}
{strip}
{if $data_orig_value ne '' or $data_orig_keep_empty eq 'Y'}
data-
{if $data_orig_type ne ''}
  {$data_orig_type|escape}
{else}
  aom
{/if}
-orig-value="{$data_orig_value|escape}"
{/if}
{/strip}
