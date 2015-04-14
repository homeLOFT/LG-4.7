{*
0a3dc9d9b0b4a57a400cf44664beaea8b4ce0341, v44 (xcart_4_7_0), 2015-01-26 17:22:03, meta.tpl, mixon

vim: set ts=2 sw=2 sts=2 et:
*}
<meta http-equiv="Content-Type" content="text/html; charset={$default_charset|default:"utf-8"}" />
<meta http-equiv="X-UA-Compatible" content="{$smarty.config.XUACompatible}" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Language" content="{if ($usertype eq "P" or $usertype eq "A") and $current_language ne ""}{$current_language|escape}{else}{$store_language|escape}{/if}" />
<meta name="robots" content="noindex, nofollow" />

<link rel="shortcut icon" type="image/png" href="{$current_location}/favicon.ico" />

{if $__frame_not_allowed}
<script type="text/javascript">
//<![CDATA[
if (top != self && top.location.hostname != self.location.hostname)
  top.location = self.location;
//]]>
</script>
{/if}
{include file="presets_js.tpl"}
<script type="text/javascript" src="{$SkinDir}/js/common.js"></script>
{if $config.Adaptives.is_first_start eq 'Y'}
<script type="text/javascript" src="{$SkinDir}/js/browser_identificator.js"></script>
{/if}
{if $webmaster_mode eq "editor"}
<script type="text/javascript">
//<![CDATA[
var store_language = "{if ($usertype eq "P" or $usertype eq "A") and $current_language ne ""}{$current_language|escape:javascript}{else}{$store_language|escape:javascript}{/if}";
var catalogs = new Object();
catalogs.admin = "{$catalogs.admin}";
catalogs.provider = "{$catalogs.provider}";
catalogs.customer = "{$catalogs.customer}";
catalogs.partner = "{$catalogs.partner}";
catalogs.images = "{$ImagesDir}";
catalogs.skin = "{$SkinDir}";
var lng_labels = [];
{foreach key=lbl_name item=lbl_val from=$webmaster_lng}
lng_labels['{$lbl_name}'] = '{$lbl_val}';
{/foreach}
var page_charset = "{$default_charset|default:"utf-8"}";
//]]>
</script>
<script type="text/javascript" language="JavaScript 1.2" src="{$SkinDir}/js/editor_common.js"></script>
{if $user_agent eq "ns"}
<script type="text/javascript" language="JavaScript 1.2" src="{$SkinDir}/js/editorns.js"></script>
{else}
<script type="text/javascript" language="JavaScript 1.2" src="{$SkinDir}/js/editor.js"></script>
{/if}
{/if}
{if $active_modules.Magnifier ne ""}
<script type="text/javascript" src="{$SkinDir}/lib/swfobject-min.js"></script>
{/if}

<script type="text/javascript">
//<![CDATA[
var lbl_error = '{$lng.lbl_error|wm_remove|escape:javascript}';
var lbl_warning = '{$lng.lbl_warning|wm_remove|escape:javascript}';
var lbl_information = '{$lng.lbl_information|wm_remove|escape:javascript}';
var lbl_go_to_last_edit_section = '{$lng.lbl_go_to_last_edit_section|wm_remove|escape:javascript}';
var topMessageDelay = [];
topMessageDelay['i'] = {$config.Appearance.delay_value|default:10}*1000;
topMessageDelay['w'] = {$config.Appearance.delay_value_w|default:60}*1000;
topMessageDelay['e'] = {$config.Appearance.delay_value_e|default:0}*1000;
//]]>
</script>

<!--[if lt IE 9]>
  <script type="text/javascript" src="{$SkinDir}/lib/jquery-min.1x.js"></script>
<![endif]-->
<!--[if gte IE 9]><!-->
  <script type="text/javascript" src="{$SkinDir}/lib/jquery-min.js"></script>
<!--<![endif]-->
{if $development_mode_enabled}
  <script type="text/javascript" src="{$SkinDir}/lib/jquery-migrate.development.js"></script>
{else}
  <script type="text/javascript" src="{$SkinDir}/lib/jquery-migrate.production.js"></script>
{/if}

<script type="text/javascript" src="{$SkinDir}/lib/cluetip/jquery.cluetip.js"></script>
<script type="text/javascript" src="{$SkinDir}/lib/jquery_cookie.js"></script>
<link rel="stylesheet" type="text/css" href="{$SkinDir}/lib/cluetip/jquery.cluetip.css" />

{if $gmap_enabled}
<script type="text/javascript">
//<![CDATA[
var gmapGeocodeError="{$lng.lbl_gmap_geocode_error}";
var lbl_close="{$lng.lbl_close}";
//]]>
</script>
<script type="text/javascript" src="{if $is_https_zone}https{else}http{/if}://maps.google.com/maps/api/js?sensor=false"></script>
<script type="text/javascript" src="{$SkinDir}/js/gmap.js"></script>
<script type="text/javascript" src="{$SkinDir}/js/modal.js"></script>
{/if}

{include file="jquery_ui.tpl"}

<script type="text/javascript">
//<![CDATA[
{literal}
$(document).ready( function() {
  $('form').not('.skip-auto-validation').each( function() {
    applyCheckOnSubmit(this);
  });

  $("input:submit, input:button, button, a.simple-button").button();
});

{/literal}
//]]>
</script>

{if $config.Appearance.enable_admin_context_help eq 'Y'}
  {include file="context_help.tpl"}
{/if}

{if $active_modules.Cloud_Search ne ""}
  {include file="modules/Cloud_Search/admin.tpl" _include_once=1}
{/if}

{if $active_modules.XOrder_Statuses ne ""}
  {load_defer file="modules/XOrder_Statuses/common.js" type="js"}
{/if}

{if $active_modules.XAuth ne '' and $xauth_include_js eq 'Y'}
<script type="text/javascript" src="{$SkinDir}/modules/XAuth/controller.js"></script>
{/if}

{load_defer file="js/ajax.js" type="js"}
{load_defer file="js/top_message.js" type="js"}
{load_defer file="js/popup_open.js" type="js"}
{load_defer file="lib/jquery.blockUI.min.js" type="js"}
{load_defer file="lib/jquery.blockUI.defaults.js" type="js"}

{load_defer file="js/sticky.js" type="js"}

{if $development_mode_enabled}
  {capture name=js_err_collector}
    window.onerror=function(msg, url, line) {ldelim}
        $("body").attr("JSError",msg + "\n" + url + ':' + line);
    {rdelim};
  {/capture}
  {load_defer file="js_err_collector" direct_info=$smarty.capture.js_err_collector type="js"}
{/if}

{load_defer_code type="css"}
{load_defer_code type="js"}
