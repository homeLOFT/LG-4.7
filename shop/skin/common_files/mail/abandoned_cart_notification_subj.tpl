{*
$Id$
vim: set ts=2 sw=2 sts=2 et:
*}
{config_load file="$skin_config"}{if $coupon}{$lng.eml_abcr_abandoned_cart_notification_with_coupon_subj|substitute:"company":$config.Company.company_name}{else}{$lng.eml_abcr_abandoned_cart_notification_without_coupon_subj|substitute:"company":$config.Company.company_name}{/if}
