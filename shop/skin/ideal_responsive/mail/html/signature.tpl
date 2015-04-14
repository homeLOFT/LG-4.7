{*
e94f191a2cc0cb8dbefd384fabaa6f48af5f3c11, v1 (xcart_4_6_2), 2013-12-25 09:14:58, signature.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{include file="mail/html/responsive_row.tpl" content=''}

{capture name="row"}
{$lng.eml_signature}
<br /><br />
<font class="text-muted">
{if $config.Company.company_name}{$config.Company.company_name}<br />{/if}
{if $config.Company.company_phone}{$lng.lbl_phone}: {$config.Company.company_phone}<br />{/if}
{if $config.Company.company_fax}{$lng.lbl_fax}:   {$config.Company.company_fax}<br />{/if}
{$lng.lbl_url}: <a href="{$http_location}/" target="_blank">{$config.Company.company_website|default:$http_location}</a>
</font>
{/capture}
{include file="mail/html/responsive_row.tpl" content=$smarty.capture.row class="footer"}
