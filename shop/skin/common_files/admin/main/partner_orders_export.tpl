{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, partner_orders_export.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{section name=ri loop=$report}
{$report[ri].orderid}{$delimiter}{$report[ri].login}{$delimiter}{$report[ri].firstname}{$delimiter}{$report[ri].lastname}{$delimiter}{$report[ri].b_address}{$delimiter}{$report[ri].b_address_2}{$delimiter}{$report[ri].b_city}{$delimiter}{$report[ri].b_state}{$delimiter}{$report[ri].b_country}{$delimiter}{$report[ri].subtotal}{$delimiter}{$report[ri].commissions}{$delimiter}{$report[ri].paid}
{/section}
