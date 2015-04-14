{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, survey_invitation_subj.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{config_load file="$skin_config"}{$config.Company.company_name}: {$lng.eml_survey_invitation_subj|substitute:"survey_name":$survey.survey}
