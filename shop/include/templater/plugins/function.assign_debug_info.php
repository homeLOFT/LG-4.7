<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Smarty {assign_debug_info} function plugin
 *
 * Type:     function<br>
 * Name:     assign_debug_info<br>
 * Purpose:  assign debug info to the template<br>
 * @author Monte Ohrt <monte at ohrt dot com>
 * @param array unused in this plugin, this plugin uses {@link Smarty::$_config},
 * @param Smarty
 */
function smarty_function_assign_debug_info($params, &$smarty)
{//{{{
    global $shop_language;

    $config_vars = $smarty->getTemplateVars('config_vars');
    if (!empty($config_vars)) {
        $smarty->assign("_debug_config_keys", array_keys($config_vars));
        $smarty->assign("_debug_config_vals", array_values($config_vars));
    }
    
    $assigned_vars = $smarty->getTemplateVars('assigned_vars');
    $smarty->assign('_debug_keys', array_keys($assigned_vars));
    $smarty->assign('_debug_vals', array_values($assigned_vars));
    
    $smarty->assignByRef('lng', $assigned_vars['lng']->value);
    $smarty->assignByRef('config', $assigned_vars['config']->value);
    $smarty->assign('catalogs', $assigned_vars['catalogs']->value);
    $smarty->assign('webmaster_mode', $assigned_vars['webmaster_mode']->value);
    $smarty->assign('opener', $assigned_vars['opener']->value);
    $smarty->assign('shop_language', $assigned_vars['shop_language']->value);
    $smarty->assign('ImagesDir', $assigned_vars['ImagesDir']->value);
    $smarty->assign('default_charset', $assigned_vars['default_charset']->value);

    $included_templates = $smarty->getTemplateVars('template_data');
    $tpl_depths = $smarty->smarty->already_included_tpls;
    foreach($tpl_depths as $short_name => $depth) {
        foreach($included_templates as $key => $template) {
            if (
                strpos($template['name'], $short_name) !== false
                && $template['name'][strpos($template['name'], '_' . $short_name)] == '/'
            ) {
                $included_templates[$key]['depth'] = $depth;
                break;
            }
        }
    }

    array_walk($included_templates, 'func_concat_array');
    $smarty->assign('_debug_tpls', $included_templates);
}//}}}

/**
 * array_walk callback routine. Checks if the template is available for reading.
 * 
 * @param array $elem array element
 *  
 * @return array
 * @see    ____func_see____
 * @since  1.0.0
 */
function func_concat_array(&$elem)
{//{{{
    global $alt_skin_info, $smarty_skin_root_dir;
    // Convert to relative path
    $elem['name'] = substr($elem['name'], strpos($elem['name'], $smarty_skin_root_dir) + strlen($smarty_skin_root_dir) + 1);

    $altName = $alt_skin_info['path'] . XC_DS . $elem['name'];

    if (
        is_file($altName)
        && is_readable($altName)
    ) {
        $elem['name'] = $alt_skin_info['alt_schemes_skin_name'] . '/' . $elem['name'];
    }

    return $elem;
}//}}}

/* vim: set expandtab: */

?>
