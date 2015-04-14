{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, partner_report_export.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{section name=ri loop=$report}
{$report[ri].login}{$delimiter}{$report[ri].firstname}{$delimiter}{$report[ri].lastname}{$delimiter}{$report[ri].sum_paid}{$delimiter}{$report[ri].sum_nopaid}{$delimiter}{$report[ri].sum}{$delimiter}{$report[ri].min_paid}
{/section}
