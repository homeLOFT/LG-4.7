{*
9801da6f17425f50e8047475e08712045e9818fd, v2 (xcart_4_6_1), 2013-08-29 12:26:36, egoods.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
<tr> 
{if $geid ne ''}<td width="15" class="TableSubHead"><input type="checkbox" value="Y" name="fields[distribution]" /></td>{/if}
  <td class="FormButton">{$lng.lbl_esd_distribution}:</td>
  <td>
{include file="main/popup_files_js.tpl"}
    <input type="hidden" name="distribution_filename" />
    <input type="text" name="distribution" size="24" value="{$product.distribution|escape}" />
    <input type="button" value="{$lng.lbl_browse_|strip_tags:false|escape}" onclick="javascript: popup_files('modifyform.distribution_filename', 'modifyform.distribution');" />
  </td>
</tr>
