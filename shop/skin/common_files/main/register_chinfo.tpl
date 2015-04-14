{*
c3f2c63d6ec12340c11b3f3d5d6a44e80a517ccb, v2 (xcart_4_4_5), 2011-12-20 09:58:43, register_chinfo.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{if $hide_header ne "Y"}
<tr valign="middle">
<td height="20" colspan="3"><font class="RegSectionTitle">{$lng.lbl_check_information}</font><hr size="1" noshade="noshade" /></td>
</tr>
{/if}

<tr valign="middle">
<td align="right" class="data-name"><label for="check_name">{$lng.lbl_ch_name}</label></td>
<td class="data-required">*</td>
<td nowrap="nowrap">
<input type="text" id="check_name" name="check_name" size="32" maxlength="128" value="{if $userinfo.lastname ne ""}{$userinfo.firstname} {$userinfo.lastname}{/if}" />
</td>
</tr>

<tr valign="middle">
<td align="right" class="data-name"><label for="check_ban">{$lng.lbl_ch_bank_account}</label></td>
<td class="data-required">*</td>
<td nowrap="nowrap">
<input type="text" id="check_ban" name="check_ban" size="32" maxlength="32" value="" />
</td>
</tr>

<tr valign="middle">
<td align="right" class="data-name"><label for="check_brn">{$lng.lbl_ch_bank_routing}</label></td>
<td class="data-required">*</td>
<td nowrap="nowrap">
<input type="text" id="check_brn" name="check_brn" size="32" maxlength="32" value="" />
</td>
</tr>

{if $payment_cc_data.disable_ccinfo eq "N"}
<tr valign="middle">
<td align="right" class="data-name"><label for="check_number">{$lng.lbl_ch_number}</label></td>
<td class="data-required">*</td>
<td nowrap="nowrap">
<input type="text" id="check_number" name="check_number" size="32" maxlength="32" value="" />
</td>
</tr>
{/if}
