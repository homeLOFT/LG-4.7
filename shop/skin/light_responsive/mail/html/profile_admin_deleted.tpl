{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, profile_admin_deleted.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{include file="mail/html/mail_header.tpl"}

{capture name="row"}
{$lng.eml_profile_deleted_admin}

<br />{$lng.lbl_profile_details}:
{/capture}
{include file="mail/html/responsive_row.tpl" content=$smarty.capture.row}

{include file="mail/html/profile_data.tpl"}

{include file="mail/html/signature.tpl"}

