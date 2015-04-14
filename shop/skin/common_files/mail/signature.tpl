{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, signature.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
--
{$lng.eml_signature}

{if $config.Company.company_name}{$config.Company.company_name}
{/if}
{if $config.Company.company_phone}{$lng.lbl_phone|mail_truncate}{$config.Company.company_phone}
{/if}
{if $config.Company.company_fax}{$lng.lbl_fax|mail_truncate}{$config.Company.company_fax}
{/if}
{$lng.lbl_url|mail_truncate}{if $config.Company.company_website ne ""} {$config.Company.company_website} ({$http_location}){else}{$http_location}{/if}
