{*
e92ea81c17578dfaea692a5eabc7e378c0c23b21, v2 (xcart_4_7_1), 2015-03-09 12:39:34, copyright.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{$lng.lbl_copyright} &copy; {$config.Company.start_year}{if $config.Company.start_year lt $config.Company.end_year}-{$smarty.now|date_format:"%Y"}{/if} {$config.Company.company_name|escape}

{if $active_modules.XMultiCurrency and $config.mc_geoip_service eq 'maxmind_free'}
. {$lng.mc_txt_maxmind_copyright}
{/if}
