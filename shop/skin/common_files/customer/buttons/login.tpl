{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, login.tpl, joy 
vim: set ts=2 sw=2 sts=2 et:
*}
{if $is_popup}
  {assign var="href" value="javascript: popupOpen('`$login_url`');"}
{else}
  {assign var="href" value="`$login_url`"}
{/if}
{assign var=bn_title value=$button_title|default:$lng.lbl_sign_in}
{include file="customer/buttons/button.tpl" button_title=$bn_title style="link" href=$href link_href="login.php"}
