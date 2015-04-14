{*
c863034063dde05a16bb4f2a2984f56cd779cc10, v7 (xcart_4_5_5), 2013-01-28 14:29:28, service_head.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{get_title page_type=$meta_page_type page_id=$meta_page_id}
{include file="customer/meta.tpl"}
{include file="customer/service_js.tpl"}
{include file="customer/service_css.tpl"}

<link rel="shortcut icon" type="image/png" href="{$current_location}/favicon.ico" />

{if $config.SEO.canonical eq 'Y'}
  <link rel="canonical" href="{$current_location}/{$canonical_url}" />
{/if}
{if $config.SEO.clean_urls_enabled eq "Y"}
  <base href="{$catalogs.customer}/" />
{/if}

{if $active_modules.Refine_Filters}
  {include file="modules/Refine_Filters/service_head.tpl"}
{/if}

{if $active_modules.Socialize ne ""}
  {include file="modules/Socialize/service_head.tpl"}
{/if}

{if $active_modules.Lexity ne ""}
  {include file="modules/Lexity/service_head.tpl"}
{/if}

{load_defer_code type="css"}
{load_defer_code type="js"}
