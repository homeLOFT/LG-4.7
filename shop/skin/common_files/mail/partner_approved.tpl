{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, partner_approved.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{include file="mail/mail_header.tpl"}

{include file="mail/salutation.tpl" title=$userinfo.title firstname=$userinfo.firstname lastname=$userinfo.lastname}

{$lng.eml_partner_approved}

{$lng.lbl_profile_details}:
---------------------
{include file="mail/profile_data.tpl" userinfo=$userinfo}

{include file="mail/signature.tpl"}
