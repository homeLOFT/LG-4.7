{*
10d4766a297b130dea8de7e1d6cd01925e213749, v4 (xcart_4_6_0), 2013-04-09 11:07:38, popup_create_thumbnail.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
{config_load file="$skin_config"}
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title>{$lng.lbl_re_create_thumbnail|wm_remove|escape}</title>
  {include file="meta.tpl"}
  {include file="service_css.tpl"}
</head>
<body{$reading_direction_tag} onload="javascript: window.focus();" style="background-color: #F0F0F0;">

<div id="create_thumbnail_div" align="center">
  <br />
  <br />
  <a href="http://www.adobe.com/go/getflashplayer" target="blank">{$lng.lbl_get_latest_flash_player}</a>
</div>
<script type="text/javascript">
//<![CDATA[
var path = "{$SkinDir}/modules/Magnifier/cutter.swf";
var w = '{$magnifier_sets.x_crt_thmb}';
var h = '{$magnifier_sets.y_crt_thmb}';
var imgPath = "{$level0_path}";
var imageid = "{$imageid}";

{literal}
swfobject.embedSWF(
  path,
  'create_thumbnail_div', 
  w, h,
  '8.0.0',
  false,
  {
    imgPath: imgPath,
    imageid: imageid,
  },
  {
    id: 'create_thumbnail',
    style: 'background-color: #f0f0f0'
  }
);
{/literal}
//]]>
</script>

</body>
</html>
