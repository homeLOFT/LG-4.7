{*
10d4766a297b130dea8de7e1d6cd01925e213749, v4 (xcart_4_6_0), 2013-04-09 11:07:38, service_header.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
{config_load file="$skin_config"}
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title>{$lng.txt_site_title}</title>
  {include file="meta.tpl"}
  {include file="service_css.tpl"}
</head>
<body{$reading_direction_tag}>
{literal}
<script type="text/javascript" language="javascript">
//<![CDATA[
function refresh()
{
    window.scroll(0, 100000);

    setTimeout('refresh()', 1000);
}
function scrollDown()
{
    setTimeout('refresh()', 1000);
}
scrollDown();
//]]>
</script>
{/literal}
<div id="head-admin">

  <div id="logo-gray">
    <a href="{$http_location}/"><img src="{$ImagesDir}/logo_gray.png" alt="" /></a>
  </div>

  <div class="clearing"></div>

</div>

<br />
