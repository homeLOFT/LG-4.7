{*
8053a11c72313915466a8a32d8de58ff13fe1be6, v1 (xcart_4_5_3), 2012-08-17 06:51:14, 1800C_reg_info.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{$lng.eml_1800c_account_info|mail_truncate}

{$lng.lbl_1800c_warehouse_name|mail_truncate} {$seller_address.company_name}
{$lng.lbl_address|mail_truncate} {$seller_address.address}
{$lng.lbl_city|mail_truncate} {$seller_address.city}
{$lng.lbl_state|mail_truncate} {$seller_address.state}
{$lng.lbl_country|mail_truncate} {$seller_address.country}
{$lng.lbl_zip_code|mail_truncate} {$seller_address.zipcode}
{$lng.lbl_phone|mail_truncate} {$seller_address.phone}
{$lng.lbl_1800c_business_hours|mail_truncate} {$seller_address.business_hours}
{$lng.lbl_1800c_operation_days|mail_truncate} {$seller_address.operation_days}

{$lng.lbl_username|mail_truncate} {$seller_address.username}
{$lng.lbl_1800c_ready_time|mail_truncate} {$seller_address.readytime}
{$lng.lbl_1800c_subsidizing_rate|mail_truncate} {$seller_address.subsidize}

{include file="mail/signature.tpl"}


