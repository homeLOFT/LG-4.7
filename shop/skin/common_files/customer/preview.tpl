{*
4eb1aaf1d647943990792fe0ae233b77c6b06383, v6 (xcart_4_6_1), 2013-06-27 17:47:34, preview.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{config_load file="$skin_config"}
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
{include file="customer/service_css.tpl"}
<meta http-equiv="Content-Type" content="text/html; charset={$default_charset|default:"utf-8"}" />
<meta http-equiv="X-UA-Compatible" content="{$smarty.config.XUACompatible}" />

</head>
<body{$reading_direction_tag}>
  <table cellpadding="10" cellspacing="10" width="100%">
    <tr>
      <td>
        {if $template}
          {assign var="this_is_printable_version" value="Y"}
          {include file=$template}
        {/if}
      </td>
    </tr>
    <tr>
      <td align="right">
        {include file="customer/buttons/button.tpl" button_title=$lng.lbl_close_window href="javascript:window.close()"}
      </td>
    </tr>
  </table>
{load_defer_code type="css"}
{load_defer_code type="js"}
</body>
</html>
