{*
10d4766a297b130dea8de7e1d6cd01925e213749, v2 (xcart_4_6_0), 2013-04-09 11:07:38, images_location_log.tpl, random
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
<table cellpadding="0" cellspacing="0" width="100%">
<tr>
  <td class="HeadLogo"><a href="{$http_location}"><img src="{$ImagesDir}/admin_xlogo.gif" alt="" /></a></td>
</tr>
</table>

<table cellpadding="0" cellspacing="0" width="100%">
<tr>
  <td class="HeadThinLine"><img src="{$ImagesDir}/spacer.gif" class="Spc" alt="" /></td>
</tr>
<tr>
  <td class="HeadLine" height="22"><img src="{$ImagesDir}/spacer.gif" width="1" height="22" alt="" /></td>
</td>
</tr>
<tr>
  <td class="HeadThinLine"><img src="{$ImagesDir}/spacer.gif" class="Spc" alt="" /></td>
</tr>
</table>

<table cellpadding="2" cellspacing="2" width="100%">
<tr>
  <td class="Header">{$lng.lbl_images_transferring_log}</td>
</tr>

<tr>
<td>

<table cellpadding="5" cellspacing="0" width="0"><tr><td>
<!-- begin -->
<pre>
{if $incfile}
{$incfile}
{else}
{$lng.lbl_log_file_empty}
{/if}
</pre>
<!-- end -->
</td></tr></table>

</td>
</tr>
</table>

</body>
</html>
