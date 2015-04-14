{*
cbbf01fde6ad5261122f17ed13914a23bd36a2ef, v19 (xcart_4_6_5), 2014-10-18 12:46:37, jquery_ui.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}

{if $development_mode_enabled}
  {load_defer file="js/jquery_ui_disable_compat.js" type="js"}
{/if}

{load_defer file="lib/jqueryui/jquery-ui.custom.min.js" type="js"}
{if $usertype eq 'C'}
  {load_defer file="js/jquery_ui_fix.js" type="js"}
  {load_defer file="lib/jqueryui/jquery.ui.theme.css" type="css"}
{else}
  {load_defer file="lib/jqueryui/datepicker_i18n/jquery-ui-i18n.js" type="js"}
  {load_defer file="js/jquery_ui_override.js" type="js"}
  {*Last loaded localization is default and used when $shop_language is not supported*}
  {load_defer file="lib/jqueryui/datepicker_i18n/jquery.ui.datepicker-en-GB.js" type="js"}
  {load_defer file="lib/jqueryui/jquery.ui.admin.css" type="css"}
{/if}
{load_defer file="css/jquery_ui.css" type="css"}
{if $config.UA.browser eq "MSIE" and $config.UA.version eq 8}
{load_defer file="css/jquery_ui.IE8.css" type="css"}
{/if}
