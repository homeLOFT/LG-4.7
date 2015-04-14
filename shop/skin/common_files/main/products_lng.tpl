{*
895a6757fade3bcc31db48359a3044a8b2afc388, v4 (xcart_4_5_0), 2012-04-06 13:01:27, products_lng.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}

<script type="text/javascript">
//<![CDATA[
var requiredFields = [
  ['product_lngproduct', "{$lng.lbl_product_title|strip_tags|wm_remove|escape:javascript}", false],
  ['product_lngdescr', "{$lng.lbl_short_description|strip_tags|wm_remove|escape:javascript}", false]
];
//]]>
</script>

{include file="check_required_fields_js.tpl"}
{capture name=dialog}
<form action="product_modify.php" method="post" name="modifylng" onsubmit="javascript: return checkRequired(requiredFields)">
<input type="hidden" name="section" value="lng" />
<input type="hidden" name="mode" value="update_lng" />
<input type="hidden" name="productid" value="{$product.productid}" />
<input type="hidden" name="geid" value="{$geid}" />

<table width="100%" {if $geid ne ''}cellspacing="0" cellpadding="4"{else}cellspacing="1" cellpadding="2"{/if} class="product-details-table">
<tr>
  {if $geid ne ''}<td width="15" class="TableSubHead">&nbsp;</td>{/if}
  <td colspan="3" align="right">
    {include file="main/language_selector.tpl" script="`$navigation_script`&"}
  </td>
</tr>

<tr>
  {if $geid ne ''}<td width="15" class="TableSubHead"><input type="checkbox" value="Y" name="fields[languages][product]" /></td>{/if}
  <td width="20%" class="FormButton" nowrap="nowrap">{$lng.lbl_product_title}* :</td>
  <td>&nbsp;</td>
  <td class="ProductDetails" width="80%"><input type="text" size="45" name="product_lng[product]" id="product_lngproduct" value="{$product_languages.product|escape:"html"}" class="InputWidth" />
  {if $top_message.fillerror ne "" and $product_languages.product eq ""}<font class="Star">&lt;&lt;</font>{/if}
  {if $config.SEO.clean_urls_enabled eq "Y"}
      <div class="SmallText">
        <input type="checkbox" name="update_clean_url" id="update_clean_url" value="Y" checked="checked" onchange="javascript:this.form.clean_url_save_in_history.disabled=!this.checked" />
        <label for="update_clean_url">{$lng.lbl_update_clean_url_from_product}</label><br />
        <input type="checkbox" name="clean_url_save_in_history" id="clean_url_save_in_history" value="Y" checked="checked" />
        <label for="clean_url_save_in_history">{$lng.lbl_clean_url_save_old}</label><br />
      </div>
  {/if}
  </td>
</tr>
<tr>
  {if $geid ne ''}<td width="15" class="TableSubHead"><input type="checkbox" value="Y" name="fields[languages][keywords]" /></td>{/if}
  <td class="FormButton" nowrap="nowrap">{$lng.lbl_keywords}:</td>
  <td>&nbsp;</td>
  <td class="ProductDetails"><input type="text" size="45" name="product_lng[keywords]" value="{$product_languages.keywords|escape:"html"}" class="InputWidth" /></td>
</tr>
<tr>
  {if $geid ne ''}<td width="15" class="TableSubHead"><input type="checkbox" value="Y" name="fields[languages][descr]" /></td>{/if}
  <td class="FormButton" nowrap="nowrap">{$lng.lbl_short_description}* :</td>
  <td>&nbsp;</td>
  <td class="ProductDetails">
{include file="main/textarea.tpl" name="product_lng[descr]" cols=45 rows=8 class="InputWidth" data=$product_languages.descr width="80%" btn_rows=4}
  {if $top_message.fillerror ne "" and $product_languages.descr eq ""}<font class="Star">&lt;&lt;</font>{/if}
  </td>
</tr>
<tr>
  {if $geid ne ''}<td width="15" class="TableSubHead"><input type="checkbox" value="Y" name="fields[languages][fulldescr]" /></td>{/if}
  <td class="FormButton" nowrap="nowrap">{$lng.lbl_det_description}:</td>
  <td>&nbsp;</td>
  <td class="ProductDetails">
{include file="main/textarea.tpl" name="product_lng[fulldescr]" cols=45 rows=12 class="InputWidth" data=$product_languages.fulldescr width="80%" btn_rows=4}
  </td>
</tr>
</table>

<br />
<hr />
<br />

<input type="submit" value="{$lng.lbl_apply|strip_tags:false|escape}" />&nbsp;&nbsp;&nbsp;

{if $shop_language ne $config.default_admin_language}
{if $geid}
<br /><br />
<table>
<tr>
  <td><input type="checkbox" id="fill_lang_all" name="fill_lang_all" value="Y" /></td>
  <td><label for="fill_lang_all">{$lng.lbl_fill_descr_from_default_for_all|substitute:'code':$config.default_admin_language}</label></td>
</tr>
</table>
{/if}
<input type="button" value="{$lng.lbl_fill_descr_from_default|strip_tags:false|escape|substitute:'code':$config.default_admin_language}" onclick="javascript: submitForm(this, 'fill_lang');" />
{/if}

</form>
{/capture}
{include file="dialog.tpl" content=$smarty.capture.dialog title=$lng.txt_international_descriptions extra='width="100%"'}
