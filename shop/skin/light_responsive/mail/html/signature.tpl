{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, signature.tpl, aim

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
