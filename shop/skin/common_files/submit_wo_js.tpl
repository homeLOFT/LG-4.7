{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, submit_wo_js.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
<input type="submit" value="{$value|strip_tags:false|escape}" /><br />
{if $note ne "off"}
<br />{$lng.txt_js_disabled_msg}
{/if}
