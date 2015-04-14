{*
e94f191a2cc0cb8dbefd384fabaa6f48af5f3c11, v1 (xcart_4_6_2), 2013-12-25 09:14:58, 1800C_reg_info.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{include file="mail/html/mail_header.tpl"}

{capture name="row"}
{$lng.eml_1800c_account_info}<br />
<br />
{$lng.lbl_1800c_warehouse_name}: {$seller_address.company_name}<br />
{$lng.lbl_address}: {$seller_address.address}<br />
{$lng.lbl_city}: {$seller_address.city}<br />
{$lng.lbl_state}: {$seller_address.state}<br />
{$lng.lbl_country}: {$seller_address.country}<br />
{$lng.lbl_zip_code}: {$seller_address.zipcode}<br />
{$lng.lbl_phone}: {$seller_address.phone}<br />
{$lng.lbl_1800c_business_hours}: {$seller_address.business_hours}<br />
{$lng.lbl_1800c_operation_days}: {$seller_address.operation_days}<br />

<br />
{$lng.lbl_username}: {$seller_address.username}<br />
{$lng.lbl_1800c_ready_time}: {$seller_address.readytime}<br />
{$lng.lbl_1800c_subsidizing_rate}: {$seller_address.subsidize}<br />
{/capture}
{include file="mail/html/responsive_row.tpl" content=$smarty.capture.row}

{include file="mail/html/signature.tpl"}
