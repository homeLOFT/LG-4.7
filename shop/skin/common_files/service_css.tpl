{*
10c0702adef39cd82ae1216e2172a4763a6e0380, v2 (xcart_4_6_5), 2014-08-13 17:48:27, service_css.tpl, mixon
vim: set ts=2 sw=2 sts=2 et:
*}
{if $config.UA.browser eq 'MSIE'}
  {assign var=ie_ver value=$config.UA.version|string_format:'%d'}
{/if}
<link rel="stylesheet" type="text/css" href="{$SkinDir}/css/admin.css" />
<link rel="stylesheet" type="text/css" href="{$SkinDir}/css/font-awesome.min.css" />
{if $ie_ver ne ''}
<style type="text/css">
<!--
{/if}
{strip}
{foreach from=$css_files item=files key=mname}
  {foreach from=$files item=f}
    {if $f.admin}
      {if not $ie_ver}
        <link rel="stylesheet" type="text/css" href="{$SkinDir}/modules/{$mname}/{$f.subpath}admin{if $f.suffix}.{$f.suffix}{/if}.css" />
      {else}
        @import url("{$SkinDir}/modules/{$mname}/{$f.subpath}admin{if $f.suffix}.{$f.suffix}{/if}.css");
      {/if}
    {/if}
  {/foreach}
{/foreach}
{/strip}
{if $ie_ver ne ''}
-->
</style>
{/if}
