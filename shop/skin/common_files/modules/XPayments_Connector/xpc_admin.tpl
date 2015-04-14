{*
ca447a3bb44a7c96fb974359eb5541ebaf014683, v4 (xcart_4_6_4), 2014-07-03 09:54:40, xpc_admin.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{include file="page_title.tpl" title=$lng.module_name_XPayments_Connector}

{capture name=dialog}
{if $mode eq 'welcome'}
  {* module is not configured *}
  <div style="display: inline-block;">
    <img src="{$ImagesDir}/xpc_logo.png" width="130" height="55" alt="X-Payments logo" />
    <br /><br />
    {$lng.txt_xpc_welcome_instructions}
    <br />
    <div style="text-align: right; margin-top: 20px;">
      <input type="button" value="{$lng.lbl_xpc_see_pricing|strip_tags:false|escape}" onclick="javascript: window.location = 'http://www.x-cart.com/xpayments-pricing.html?utm_source=xcart&utm_medium=welcome_page&utm_campaign=xp_connector';" />
      <span class="main-button" style="margin-left: 20px;">
        <input type="button" value="{$lng.lbl_next|strip_tags:false|escape}" onclick="javascript: window.location = 'xpc_admin.php?mode=deploy_configuration';" />
      </span>
    </div>
  </div>
{elseif $mode eq 'deploy_configuration'}
  {* admin clicked next on first step *}
  {include file="modules/XPayments_Connector/admin_deploy.tpl"}
{else}
  {* regular settings page *}
  {include file="modules/XPayments_Connector/config_recommends.tpl"}
  {include file="customer/main/ui_tabs.tpl" prefix="xpc-tabs-" mode="inline" default_tab="-1last_used_tab" tabs=$xpc_config_tabs}
{/if}

{/capture}
{include file="dialog.tpl" content=$smarty.capture.dialog extra='width="100%"'}
