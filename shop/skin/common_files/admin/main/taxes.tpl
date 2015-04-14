{*
baa47053f8a3195eecd661f42434e7ba22e88883, v13 (xcart_4_7_0), 2015-02-03 17:38:36, taxes.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{include file="page_title.tpl" title=$lng.lbl_taxes}

{if $active_modules.TaxCloud eq "Y"}
{include file="modules/TaxCloud/taxes.tpl"}

{elseif $active_modules.AvaTax eq "Y"}
{include file="modules/AvaTax/taxes.tpl"}

{else}

{if $lng.txt_taxes_avatax_advertisement ne ''}
  {$lng.txt_taxes_avatax_advertisement}
{else}
  {$lng.txt_taxes_general_note}
{/if}

<br /><br />

<br />

{capture name=dialog}

{if $taxes}
{assign var="colspan" value="5"}

<script type="text/javascript" language="JavaScript 1.2">
//<![CDATA[
checkboxes_form = 'taxesform';
checkboxes = new Array({foreach from=$taxes item=v key=k}{if $k gt 0},{/if}'to_delete[{$v.taxid}]'{/foreach});

//]]>
</script>
<script type="text/javascript" src="{$SkinDir}/js/change_all_checkboxes.js"></script>

<div style="line-height:170%"><a href="javascript:change_all(true);">{$lng.lbl_check_all}</a> / <a href="javascript:change_all(false);">{$lng.lbl_uncheck_all}</a></div>

{else}
{assign var="colspan" value="4"}
{/if}

<form action="taxes.php" method="post" name="taxesform">
<input type="hidden" name="mode" value="update" />

<table cellpadding="3" cellspacing="1" width="100%">

<tr class="TableHead">
  {if $taxes}<td>&nbsp;</td>{/if}
  <td width="30%">{$lng.lbl_tax_name}</td>
  <td width="30%" align="center">{$lng.lbl_tax_apply_to}</td>
  <td width="20%" align="center">{$lng.lbl_tax_priority}</td>
  <td width="20%" align="center">{$lng.lbl_status}</td>
</tr>

{if $taxes}

{section name=tax loop=$taxes}

<tr{cycle values=", class='TableSubHead'"}>
  <td><input type="checkbox" name="to_delete[{$taxes[tax].taxid}]" /></td>
  <td class="FormButton"><a href="taxes.php?taxid={$taxes[tax].taxid}">{$taxes[tax].tax_name|replace:" ":"&nbsp;"}</a>
{if $is_admin_user}
({$lng.txt_N_rates_defined|substitute:"rates":$taxes[tax].rates_count})
{/if}
  </td>
  <td align="center"><a href="taxes.php?taxid={$taxes[tax].taxid}">{$taxes[tax].formula}</a></td>
  <td align="center"><input type="text" size="5" name="posted_data[{$taxes[tax].taxid}][tax_priority]" value="{$taxes[tax].priority}" /></td>
  <td align="center">
  <select name="posted_data[{$taxes[tax].taxid}][active]">
    <option value="Y"{if $taxes[tax].active eq "Y"} selected="selected"{/if}>{$lng.lbl_enabled}</option>
    <option value="N"{if $taxes[tax].active eq "N"} selected="selected"{/if}>{$lng.lbl_disabled}</option>
  </select>
  </td>
</tr>

{/section}

<tr>
  <td colspan="{$colspan}" class="SubmitBox">
<input type="button" value="{$lng.lbl_delete_selected|strip_tags:false|escape}" onclick="javascript: if (checkMarks(this.form, new RegExp('to_delete\[[0-9]+\]', 'gi'))) submitForm(this, 'delete');" />
<input type="submit" value="{$lng.lbl_update|strip_tags:false|escape}" />
{if $active_modules.Simple_Mode}
<input type="button" value="{$lng.lbl_apply_selected_taxes_to_all_products|strip_tags:false|escape}" onclick="javascript: if (checkMarks(this.form, new RegExp('to_delete\[[0-9]+\]', 'gi'))) submitForm(this, 'apply');" />
{/if}
  </td>
</tr>

{else}

<tr>
  <td colspan="{$colspan}" align="center">{$lng.txt_no_taxes_defined}</td>
</tr>

{/if}

</table>
</form>

<br /><br />

<input type="button" value="{$lng.lbl_add_new_|strip_tags:false|escape}" onclick="javascript: self.location='taxes.php?mode=add';" />

<br />

{/capture}
{include file="dialog.tpl" title=$lng.lbl_taxes content=$smarty.capture.dialog extra='width="100%"'}

{if $taxes}

<br /><br />

{capture name=dialog}

<form action="taxes.php" method="post" name="taxesform_options">
<input type="hidden" name="mode" value="tax_options" />
<input type="hidden" name="posted_data[display_taxed_order_totals]" value="N" />
<input type="hidden" name="posted_data[display_cart_products_tax_rates]" value="N" />
<input type="hidden" name="posted_data[tax_operation_scheme]" value="TAX_SCHEME_GENERAL" />
<input type="hidden" name="posted_data[allow_user_modify_tax_number]" value="N" />

<table cellpadding="3" cellspacing="1">

<tr>
  <td width="50%"><label for="display_taxed_order_totals">{$lng.opt_display_taxed_order_totals}:</label></td>
  <td width="50%">
<input type="checkbox" id="display_taxed_order_totals" name="posted_data[display_taxed_order_totals]" value="Y"{if $config.Taxes.display_taxed_order_totals eq "Y"} checked="checked"{/if} />
  </td>
</tr>

<tr>
  <td><label for="display_cart_products_tax_rates">{$lng.opt_display_cart_products_tax_rates}:</label></td>
  <td>
<input type="checkbox" id="display_cart_products_tax_rates" name="posted_data[display_cart_products_tax_rates]" value="Y"{if $config.Taxes.display_cart_products_tax_rates eq "Y"} checked="checked"{/if} />
  </td>
</tr>

<tr>
    <td><label for="tax_operation_scheme">{$lng.opt_tax_operation_scheme}:</label></td>
    <td>
<select id="tax_operation_scheme" name="posted_data[tax_operation_scheme]">
    {* Options defined as XCTaxesDefs class constants *}
    <option value="TAX_SCHEME_GENERAL" {if $config.Taxes.tax_operation_scheme eq "TAX_SCHEME_GENERAL"}selected="selected"{/if}>{$lng.opt_calculate_in_general_way}</option>
    <option value="TAX_SCHEME_NO_TAXES" {if $config.Taxes.tax_operation_scheme eq "TAX_SCHEME_NO_TAXES"}selected="selected"{/if}>{$lng.opt_do_not_add_taxes}</option>
    <option value="TAX_SCHEME_NO_TAXES_FOR_VALIDATED" {if $config.Taxes.tax_operation_scheme eq "TAX_SCHEME_NO_TAXES_FOR_VALIDATED"}selected="selected"{/if}>{$lng.opt_do_not_add_taxes_only_if_taxnumber_is_valid}</option>
</select>
    {include file="main/tooltip_js.tpl" text=$lng.txt_tax_operation_scheme_tooltip id="help_tax_operation_scheme" type='img' sticky=true}
    </td>
</tr>

<tr>
  <td><label for="allow_user_modify_tax_number">{$lng.opt_allow_user_modify_tax_number}:</label></td>
  <td>
<input type="checkbox" id="allow_user_modify_tax_number" name="posted_data[allow_user_modify_tax_number]" value="Y"{if $config.Taxes.allow_user_modify_tax_number eq "Y"} checked="checked"{/if} />
  </td>
</tr>

<tr>
  <td colspan="2" class="SubmitBox"><input type="submit" value="{$lng.lbl_update|strip_tags:false|escape}" /></td>
</tr>

</table>
</form>

{/capture}
{include file="dialog.tpl" title=$lng.option_title_Taxes content=$smarty.capture.dialog extra='width="100%"'}

{/if}

{/if}
