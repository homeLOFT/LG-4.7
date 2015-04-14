{*
49bfbae79247f9b1ef10ba71dc79b3c972bc3e3c, v5 (xcart_4_7_0), 2015-02-27 09:24:08, copyright.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{$lng.lbl_copyright} &copy; {$config.Company.start_year}{if $config.Company.start_year lt $config.Company.end_year}-{$smarty.now|date_format:"%Y"}{/if} {$config.Company.company_name|escape}

{if $active_modules.XMultiCurrency and $config.mc_geoip_service eq 'maxmind_free'}
. {$lng.mc_txt_maxmind_copyright}
{/if}

{if $active_modules.Socialize}
  {include file="modules/Socialize/footer_links.tpl"}
{/if}
