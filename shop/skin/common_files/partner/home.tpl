{*
d1ec7a094f2d7056d2e00dddfdf1d39be41d2397, v10 (xcart_4_6_1), 2013-06-10 12:27:37, home.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
{config_load file="$skin_config"}
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
{get_title}
{include file="meta.tpl"}
{include file="service_css.tpl"}
</head>
<body{$reading_direction_tag}{if $login eq ""} class="not-logged-in"{/if}>
{include file="rectangle_top.tpl"}
{include file="head_admin.tpl" menu="partner"}
<!-- main area -->
<table width="100%" cellpadding="0" cellspacing="0" align="center">
<tr>
<td colspan="2" id="location_and_status">
  {include file="location.tpl"}
</td>
</tr>
<tr>
<td valign="top" class="central-space{if $dialog_tools_data} dtools{elseif $banner_tools_data} btools{/if}">
<!-- central space -->
{if $main eq "authentication"}
{include file="main/authentication.tpl" login_title=$lng.lbl_partner_login_title is_register=$config.XAffiliate.partner_register}

{elseif $main eq "stats"}
{include file="partner/main/stats.tpl"}

{elseif $main eq "module_disabled"}
{include file="partner/main/module_disabled.tpl"}

{elseif $main eq "banner_info"}
{include file="partner/main/banner_info.tpl"}

{elseif $main eq "referred_sales"}
{include file="main/referred_sales.tpl"}

{elseif $main eq "register"}
{include file="partner/main/register.tpl"}

{elseif $main eq "payment_history"}
{include file="partner/main/payment_history.tpl"}

{elseif $main eq "affiliates"}
{include file="main/affiliates.tpl"}

{elseif $main eq "partner_banners"}
{include file="main/partner_banners.tpl"}

{elseif $main eq "products"}
{include file="main/affiliate_search_result.tpl"}

{elseif $main eq "home" and $login ne ""}
{include file="partner/main/promotions.tpl"}

{elseif $main eq "home" and $mode eq 'profile_created'}
{include file="partner/main/welcome_queued.tpl"}

{elseif $main eq "change_password"}
{include file="main/change_password.tpl"}

{else}
{include file="common_templates.tpl"}
{/if}

<!-- /central space -->
&nbsp;
</td>

<td valign="top">
  {include file="dialog_tools.tpl"}
</td>

</tr>
</table>
{include file="rectangle_bottom.tpl"}
</body>
</html>
