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

{* BCSE Begin - Google Analytics 4 *}
{if $active_modules.Bcse_Ga4}
  {include file="modules/Bcse_Ga4/service_head.tpl"}
{/if}
{* BCSE End*}

{load_defer_code type="css"}
{load_defer_code type="js"}

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&family=Open+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="/common/css/lander.css" />
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>  

{* WCM - Dynamic Product Tabs *}
{if $main eq "product" AND $active_modules.WCM_Dynamic_Product_Tabs}
<script language="javascript" type="text/javascript" src="{$SkinDir}/modules/DynamicProductTabs/jquery.tabs.pack.js"></script>
<script type="text/javascript" language="javascript">
<!--
{literal}
$(function() { $(".wcmtabcontainer").tabs(); });
{/literal} -->
</script>
{* Get the Skin Name and then load the skin using it *} 
<link rel="stylesheet" href="{$SkinDir}/modules/DynamicProductTabs/themes/{$config.WCM_Dynamic_Product_Tabs.color_theme}/jquery.tabs.css" />
<!-- Additional IE/Win specific style sheet (Conditional Comments) -->
<!--[if lte IE 7]>
<link rel="stylesheet" href="{$SkinDir}/modules/DynamicProductTabs/jquery.tabs-ie.css" type="text/css" media="projection, screen">
<![endif]-->
{/if}
{* / WCM - Dynamic Product Tabs *}
