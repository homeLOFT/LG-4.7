{*
ba427435e445c20d339986d593aa3b9436a77c65, v10 (xcart_4_7_0), 2015-01-22 15:12:11, debug_templates.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}


{*

All variables from this files have to be defined in
include/get_language.php
$predefined_lng_variables = (!empty($smarty->webmaster_mode) || $smarty->debugging)
    ? array(
        'lbl_execution_time',
        'lbl_included_templates_config_files',
        'lbl_xcart_debugging_console',
        'txt_assigned_config_file_variables',
        'txt_assigned_template_variables',
        'txt_no_config_vars_assigned',
        'txt_no_template_variables_assigned',
    )

and in 

smarty_function_assign_debug_info
include/templater/plugins/function.assign_debug_info.php

*}

{assign_debug_info}

<script type="text/javascript">
//<![CDATA[
var local_opener_name = "{$opener|escape:javascript}";
var local_lbl_xcart_debugging_console = "{$lng.lbl_xcart_debugging_console|strip_tags|wm_remove|escape:javascript}";
var local_lbl_included_templates_config_files = "{$lng.lbl_included_templates_config_files|strip_tags|wm_remove|escape:javascript}";
var local_txt_assigned_template_variables = "{$lng.txt_assigned_template_variables|strip_tags|wm_remove|escape:javascript}";
var local_txt_no_template_variables_assigned = "{$lng.txt_no_template_variables_assigned|strip_tags|wm_remove|escape:javascript}";
var local_txt_assigned_config_file_variables = "{$lng.txt_assigned_config_file_variables|strip_tags|wm_remove|escape:javascript}";
var local_txt_no_config_vars_assigned = "{$lng.txt_no_config_vars_assigned|strip_tags|wm_remove|escape:javascript}";
var local_images_dir = "{$ImagesDir|escape:javascript}";
var display_templater_vars_in_popup = true;
var default_charset = "{$default_charset|default:'utf-8'|escape:javascript}";
var shop_language = "{$shop_language|escape:javascript}";

{literal}
if (window.opener == null || local_opener_name != "console") {

  _smarty_console = window.open("", "console", "width=630,height=700,resizable,scrollbars=yes");

  var btn_switch = '';
  if (display_templater_vars_in_popup) {
    btn_switch = '<input onclick="document.getElementById(\'templates\').style.display=\'\'; document.getElementById(\'vars\').style.display=\'none\'; this.disabled=true; document.getElementById(\'var_button\').disabled=false;" type="button" id="tpl_button" disabled="disabled" value="Show templates">&nbsp;<input onclick="document.getElementById(\'vars\').style.display=\'\'; document.getElementById(\'templates\').style.display=\'none\'; this.disabled=true; document.getElementById(\'tpl_button\').disabled=false;" type="button" value="Show variables" id="var_button"> <br /> <br />';
  }

  try {
    if (_smarty_console) {
      _smarty_console.document.open();
      _smarty_console.document.write(
        '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'
        + '<html xmlns="http://www.w3.org/1999/xhtml">'
        + '<head><title>' + local_lbl_xcart_debugging_console + '<'+'/title>'
        + '<meta http-equiv="Content-Type" content="text/html; charset=' + default_charset + '" />'
        + '<meta http-equiv="Content-Script-Type" content="text/javascript" />'
        + '<meta http-equiv="Content-Style-Type" content="text/css" />'
        + '<meta http-equiv="Content-Language" content="' + shop_language + '" />'
        + '<meta name="ROBOTS" content="NOINDEX,NOFOLLOW" />'
        + '<style type="text/css">' + "\n"
        + ' html, body { font-size: 12px; padding: 0px; margin: 5px; } ' + "\n"
        + ' ul { padding: 0px 0px 0px 20px; margin: 0px 0px 10px 0px; } ' + "\n"
        + ' li { list-style-image: url(' + local_images_dir + '/rarrow.gif); white-space: nowrap; color: black; } ' + "\n"
        + ' li a:link, li a:visited, li a:hover, li a:active { color: brown; text-decoration: none; font-size: 1em; } ' + "\n"
        + ' li.ft-template { color: brown; } ' + "\n"
        + ' li.empty { background: #eeeeee none; } ' + "\n"
        + ' table.vars { font-size: 1em; margin-bottom: 10px; } ' + "\n"
        + ' table.vars tr td { background: #fefefe none; white-space: nowrap; padding: 3px; font-size: 1em; font-family: monospace; } ' + "\n"
        + ' table.vars tr.line td { background: #eeeeee none; } ' + "\n"
        + ' table.vars tr td.name { color: blue; vertical-align: top; } ' + "\n"
        + ' h1 { width: 600px; white-space: nowrap; background: #cccccc none; text-align: center; font-weight: bold; font-size: 1.3em; margin: 0px; padding: 0px; } ' + "\n"
        + ' span.file { color: black; } ' + "\n"
        + ' em.time { padding-left: 2em; font-size: 0.9em; color: black; } ' + "\n"
        + '<'+'/style>'
        + '<'+'/head><body>'
        + btn_switch
        + '<h1>' + local_lbl_included_templates_config_files + '<'+'/h1>'
        + '<ul id="templates">'
      );
{/literal}
{foreach from=$_debug_tpls item=t name=_debug_tpls}
      _smarty_console.document.write(
        '<li style="margin-left: {$t.depth}em;" class="ft-template">'
{if $webmaster_mode eq "editor"}
        + '<a hr'+'ef="{$catalogs.admin}/file_edit.php?file=%2F{$t.name|escape:url}&amp;opener=console" target="_blank" onmouseover="javascript: if (window.mainWnd && mainWnd.tmo) mainWnd.tmo(\'{$t.name|replace:"/":"0"}\', this);">{$t.name}<'+'/a>'
{else}
        + '{$t.name}'
{/if}
         + '<em class="time" title="{$lng.lbl_execution_time|strip_tags|wm_remove|escape}">({$t.render_time|string_format:"%.5f"}){if $smarty.foreach._debug_tpls.first} (total){/if}<'+'/em>'
        + '<'+'/li>'
      );
{foreachelse}
      _smarty_console.document.write('<li class="empty"><em>no templates included<'+'/em<'+'/li>');  
{/foreach}
{literal}
      _smarty_console.document.write('<'+'/ul>');
  
      if (display_templater_vars_in_popup) {
        _smarty_console.document.write(
          '<div id="vars" style="display:none;">' +
          '<h1>' + local_txt_assigned_template_variables + '<'+'/h1>' +
          '<table cellspacing="1" class="vars">'
        );
        {/literal}
          {section name=vars loop=$_debug_keys}
            _smarty_console.document.write('<tr{if $smarty.section.vars.index is even} class="line"{/if}><td class="name">{ldelim}${$_debug_keys[vars]}{rdelim}</td><td>{$_debug_vals[vars]|@debug_print_var|escape:javascript}</td></tr>');
          {sectionelse}
            _smarty_console.document.write('<tr class="line"><td colspan="2"><em>' + local_txt_no_template_variables_assigned + '</em></td></tr>');
          {/section}
        {literal}
        _smarty_console.document.write('</table>');

        _smarty_console.document.write(
          '<h1>' + local_txt_assigned_config_file_variables + '<'+'/h1>' +
          '<table cellspacing="1" class="vars">'
        );
        {/literal}
          {section name=vars loop=$_debug_config_keys}
            _smarty_console.document.write('<tr{if $smarty.section.vars.index is even} class="line"{/if}><td class="name">{ldelim}${$_debug_config_keys[vars]}{rdelim}</td><td>{$_debug_config_vals[vars]|@debug_print_var|escape:javascript}</td></tr>');
          {sectionelse}
            _smarty_console.document.write('<tr class="line"><td colspan="2"><em>' + local_txt_no_config_vars_assigned + '</em></td></tr>');
          {/section}
        {literal}
        _smarty_console.document.write('</table>');
        _smarty_console.document.write('</div>');
      }

      _smarty_console.document.write('<'+'/body><'+'/html>');
      _smarty_console.document.close();
      _smarty_console.mainWnd = window;

    }
  } catch(e) {
  }
}
{/literal}
//]]>
</script>
