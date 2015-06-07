{*
a4932c666a373247bd7f62cfd9a6313e7863b1cc, v11 (xcart_4_7_1), 2015-03-10 17:25:09, register_account.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{if $hide_header eq ""}
<tr>
<td colspan="3" class="RegSectionTitle">{$lng.lbl_account_information}<hr size="1" noshade="noshade" /></td>
</tr>
{/if}

<tr>
<td class="data-name" align="right"><label for="email">{$lng.lbl_email}</label></td>
<td class="data-required">*</td>
<td nowrap="nowrap">
<input type="text" id="email" name="email" size="32" class="input-email" maxlength="128" value="{$userinfo.email|escape}" />
</td>
</tr>

{if $userinfo.id eq $logged_userid and $logged_userid gt 0 and $userinfo.usertype ne "C"}

{* Display membership level *}

<tr style="display: none;">
<td>
<input type="hidden" name="membershipid" value="{$userinfo.membershipid}" />
<input type="hidden" name="pending_membershipid" value="{$userinfo.pending_membershipid}" />
</td>
</tr>

{else}

{if $config.General.membership_signup eq "Y" and ($usertype eq "C" or $is_admin_user or $usertype eq "B") and $membership_levels}
{include file="admin/main/membership_signup.tpl"}
{/if}

{if $usertype eq "A" or ($usertype eq "P" and $active_modules.Simple_Mode ne "") and $membership_levels}
{include file="admin/main/membership.tpl"}
{/if}

{* /Display membership level *}

{/if}

{if $config.email_as_login ne 'Y'}
<tr>
<td align="right" class="data-name"><label for="uname">{$lng.lbl_username}</label></td>
{if $userinfo.login ne '' and $config.General.allow_change_login ne 'Y'}
<td></td>
<td nowrap="nowrap">
<b>{$userinfo.login|default:$userinfo.uname}</b>
<input type="hidden" name="uname" value="{$userinfo.login|default:$userinfo.uname}" />
{else}
<td class="data-required">*</td>
<td nowrap="nowrap">
<input type="text" id="uname" name="uname" size="32" maxlength="128" value="{if $userinfo.uname}{$userinfo.uname|escape}{else}{$userinfo.login|escape}{/if}" />
{/if}
</td>
</tr>
{/if}

{if $allow_pwd_modify eq 'Y'}
<tr style="display:none;"><td><input type="hidden" name="password_is_modified" value="N" /></td></tr>
<tr>
<td align="right" class="data-name"><label for="passwd1">{$lng.lbl_password}</label></td>
{if $is_admin_user and $main ne 'user_add'}
<td>{include file="main/tooltip_js.tpl" type="img" id="keep_it_unchanged" text=$lng.lbl_keep_pass_unchanged}</td>
{else}
<td class="data-required">*</td>
{/if}
<td nowrap="nowrap"><input type="password" id="passwd1" name="passwd1" size="32" maxlength="64" value="" onchange="javascript: this.form.elements.namedItem('password_is_modified').value = 'Y';"{if $config.Security.use_complex_pwd eq 'Y'}  onblur="javascript: $('#passwd_note').hide();" onfocus="javascript: showNote('passwd_note', this);"{/if} autocomplete="off" />
{if $config.Security.use_complex_pwd eq 'Y'}<div id="passwd_note" class="NoteBox" style="display: none;">{$lng.txt_password_strength}<br /></div>{/if}
</td>
</tr>

<tr>
<td align="right" class="data-name"><label for="passwd2">{$lng.lbl_confirm_password}</label></td>
{if $is_admin_user and $main ne 'user_add'}
<td>&nbsp;</td>
{else}
<td class="data-required">*</td>
{/if}
<td nowrap="nowrap"><input type="password" id="passwd2" name="passwd2" size="32" maxlength="64" value="" onchange="javascript: this.form.elements.namedItem('password_is_modified').value = 'Y';"{if $config.Security.use_complex_pwd eq 'Y'}  onblur="javascript: $('#passwd_note').hide();" onfocus="javascript: showNote('passwd_note', this.form.elements.namedItem('passwd1'));"{/if} autocomplete="off" />
</td>
</tr>
{else}
<tr>
<td align="right" class="data-name">{$lng.lbl_password}</td>
<td></td>
<td><a href="change_password.php">{$lng.lbl_chpass}</a></td>
</tr>
{/if}

{if $is_admin_user and $userinfo.id ne $logged_userid}

<tr valign="middle">
<td align="right" class="data-name">{$lng.lbl_account_status}:</td>
<td>&nbsp;</td>
<td nowrap="nowrap">
<select name="status" id="status">
<option value="N"{if $userinfo.status eq "N"} selected="selected"{/if}>{$lng.lbl_account_status_suspended}</option>
<option value="Y"{if $userinfo.status eq "Y"} selected="selected"{/if}>{$lng.lbl_account_status_enabled}</option>
{if ($active_modules.XAffiliate ne "" and ($userinfo.usertype eq "B" or $smarty.get.usertype eq "B")) or ($userinfo.usertype eq "P" or $smarty.get.usertype eq "P")}
<option value="Q"{if $userinfo.status eq "Q"} selected="selected"{/if}>{$lng.lbl_account_status_not_approved}</option>
<option value="D"{if $userinfo.status eq "D"} selected="selected"{/if}>{$lng.lbl_account_status_declined}</option>
{/if}
</select>
</td>
</tr>

{if $display_activity_box eq "Y"}
<tr valign="middle">
<td align="right" class="data-name">{$lng.lbl_account_activity}:</td>
<td>&nbsp;</td>
<td nowrap="nowrap">
<select name="activity" id="activity">
<option value="Y"{if $userinfo.activity eq "Y"} selected="selected"{/if}>{$lng.lbl_account_activity_enabled}</option>
<option value="N"{if $userinfo.activity eq "N"} selected="selected"{/if}>{$lng.lbl_account_activity_disabled}</option>
</select>
</td>
</tr>
{/if}

<tr valign="middle">
  <td colspan="2">&nbsp;</td>
  <td nowrap="nowrap">

<table>
<tr>
  <td><input type="checkbox" id="change_password" name="change_password" value="Y"{if $userinfo.change_password eq "Y"} checked="checked"{/if} /></td>
  <td><label for="change_password">{$lng.lbl_reg_chpass}</label></td>
</tr>
</table>

  </td>
</tr>

{if ($userinfo.usertype eq "P" or $smarty.get.usertype eq "P") and $usertype eq "A" and $active_modules.Simple_Mode eq ""}
<tr valign="middle">
  <td colspan="2">&nbsp;</td>
  <td nowrap="nowrap">

<table>
<tr>
  <td><input type="checkbox" id="trusted_provider" name="trusted_provider" value="Y"{if $userinfo.trusted_provider eq "Y"} checked="checked"{/if} /></td>
  <td><label for="trusted_provider">{$lng.lbl_trusted_providers}</label></td>
</tr>
</table>

  </td>
</tr>
{/if}

{/if}
