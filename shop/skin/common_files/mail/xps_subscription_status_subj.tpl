{*
44c726a5f2719901cc8ad0f1101dcc0f40dd3c53, v1 (xcart_4_6_2), 2014-01-17 13:05:54, xps_subscription_status_subj.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{config_load file="$skin_config"}{$config.Company.company_name}: {strip}
{if $subscription.status eq 'S'}
{$lng.eml_xps_subscription_stopped_subj}
{elseif $subscription.status eq 'A'}
{$lng.eml_xps_subscription_restarted_subj}
{elseif $subscription.status eq 'F'}
{$lng.eml_xps_subscription_finished_subj}
{/if}
{/strip}
