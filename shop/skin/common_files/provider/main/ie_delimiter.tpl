{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, ie_delimiter.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{if $saved_delimiter eq ''}{assign var="saved_delimiter" value=$smarty.get.delimiter}{/if}
<select name="{$field_name|default:"delimiter"}">
  <option value=";"{if $saved_delimiter eq ";"} selected="selected"{/if}>{$lng.lbl_semicolon}</option>
  <option value=","{if $saved_delimiter eq ","} selected="selected"{/if}>{$lng.lbl_comma}</option>
  <option value="tab"{if $saved_delimiter eq "\t"} selected="selected"{/if}>{$lng.lbl_tab}</option>
</select>
