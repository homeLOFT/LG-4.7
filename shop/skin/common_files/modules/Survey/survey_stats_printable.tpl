{*
10d4766a297b130dea8de7e1d6cd01925e213749, v2 (xcart_4_6_0), 2013-04-09 11:07:38, survey_stats_printable.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
{config_load file="$skin_config"}
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>{$survey.survey}</title>
{include file="meta.tpl"}
{include file="service_css.tpl"}
</head>
<body{$reading_direction_tag}>

<table cellpadding="10" cellspacing="0">
<tr>
  <td>
{include file="modules/Survey/survey_stats.tpl"}
  </td>
</tr>
</table>

</body>
</html>
