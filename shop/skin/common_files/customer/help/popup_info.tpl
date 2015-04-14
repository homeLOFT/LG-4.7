{*
f2c96ddb72c96605401a2025154fc219a84e9e75, v8 (xcart_4_6_1), 2013-08-19 12:16:49, popup_info.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
<?xml version="1.0" encoding="{$default_charset|default:"utf-8"}"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
{config_load file="$skin_config"}
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  {include file="customer/service_head.tpl"}
  <link rel="stylesheet" type="text/css" href="{$SkinDir}/css/{#CSSFilePrefix#}.popup.css" />
</head>
<body{$reading_direction_tag}{if $body_onload ne ''} onload="javascript: {$body_onload}"{/if} class="{if $smarty.get.open_in_layer} popup-in-layer{/if}{foreach from=$container_classes item=c}{$c} {/foreach}">
<div id="page-container">
  <div id="page-container2">
    <div id="content-container">
      <div id="content-container2">
        <div id="center">
          <div id="center-main">

<!-- MAIN -->

{if $template_name ne ""}
{include file=$template_name}

{elseif $pre ne ""}
{$pre}

{else}
{include file="main/error_page_not_found.tpl"}
{/if}

<!-- /MAIN -->
          </div>
        </div>
      </div>
    </div>

    <div class="clearing">&nbsp;</div>

    <div id="header">
      <div>
        {$popup_title|default:"&nbsp;"}
      </div>
    </div>

    <div id="footer">
      <div>
        <a href="javascript:void(0);" onclick="javascript: window.close();">{$lng.lbl_close_window}</a>
      </div>
    </div>

{if $active_modules.Google_Analytics ne "" and $config.Google_Analytics.ganalytics_version eq 'Traditional'}
  {include file="modules/Google_Analytics/ga_code.tpl"}
{/if}

  </div>
</div>

{load_defer_code type="css"}
{load_defer_code type="js"}
</body>
</html>
