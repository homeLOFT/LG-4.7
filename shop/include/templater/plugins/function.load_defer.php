<?php
/* vim: set ts=4 sw=4 sts=4 et: */
/*****************************************************************************\
+-----------------------------------------------------------------------------+
| X-Cart Software license agreement                                           |
| Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>            |
| All rights reserved.                                                        |
+-----------------------------------------------------------------------------+
| PLEASE READ  THE FULL TEXT OF SOFTWARE LICENSE AGREEMENT IN THE "COPYRIGHT" |
| FILE PROVIDED WITH THIS DISTRIBUTION. THE AGREEMENT TEXT IS ALSO AVAILABLE  |
| AT THE FOLLOWING URL: http://www.x-cart.com/license.php                     |
|                                                                             |
| THIS AGREEMENT EXPRESSES THE TERMS AND CONDITIONS ON WHICH YOU MAY USE THIS |
| SOFTWARE PROGRAM AND ASSOCIATED DOCUMENTATION THAT QUALITEAM SOFTWARE LTD   |
| (hereinafter referred to as "THE AUTHOR") OF REPUBLIC OF CYPRUS IS          |
| FURNISHING OR MAKING AVAILABLE TO YOU WITH THIS AGREEMENT (COLLECTIVELY,    |
| THE "SOFTWARE"). PLEASE REVIEW THE FOLLOWING TERMS AND CONDITIONS OF THIS   |
| LICENSE AGREEMENT CAREFULLY BEFORE INSTALLING OR USING THE SOFTWARE. BY     |
| INSTALLING, COPYING OR OTHERWISE USING THE SOFTWARE, YOU AND YOUR COMPANY   |
| (COLLECTIVELY, "YOU") ARE ACCEPTING AND AGREEING TO THE TERMS OF THIS       |
| LICENSE AGREEMENT. IF YOU ARE NOT WILLING TO BE BOUND BY THIS AGREEMENT, DO |
| NOT INSTALL OR USE THE SOFTWARE. VARIOUS COPYRIGHTS AND OTHER INTELLECTUAL  |
| PROPERTY RIGHTS PROTECT THE SOFTWARE. THIS AGREEMENT IS A LICENSE AGREEMENT |
| THAT GIVES YOU LIMITED RIGHTS TO USE THE SOFTWARE AND NOT AN AGREEMENT FOR  |
| SALE OR FOR TRANSFER OF TITLE. THE AUTHOR RETAINS ALL RIGHTS NOT EXPRESSLY  |
| GRANTED BY THIS AGREEMENT.                                                  |
+-----------------------------------------------------------------------------+
\*****************************************************************************/

/**
 * Defer loading plugin.
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v39 (xcart_4_7_0), 2015-02-17 23:56:28, function.load_defer.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

// Templater plugin
// -------------------------------------------------------------
// Type:     function
// Name:     load_defer
// Input:    file
// -------------------------------------------------------------

if (!defined('XCART_START')) { header("Location: ../../../"); die("Access denied"); }

/**
 * Javascript defer plugins. Registers files to use in defer loading
 *
 * @param array  $params should have 'file' and 'type' elements
 * @param Smarty $smarty Smarty object
 *
 * @return string always empty string
 * @see    ____func_see____
 * @since  1.0.0
 */
function smarty_function_load_defer($params, &$smarty)
{
    global $xcart_web_dir, $xcart_dir, $smarty_skin_dir, $deferRegistry, $directInfoRegistry, $config, $alt_skin_info, $alt_skin_dir;
    global $already_included_files;

    if (is_null($already_included_files)) {
        $already_included_files = array();
    }

    if (
        !isset($params['file'])
        || empty($params['file'])
    ) {

        return '';
    }

    if (
        !isset($params['type'])
        || empty($params['type'])
        || !in_array($params['type'], array('js', 'css'))
    ) {

        return '';
    }

    if (!empty($alt_skin_dir)) {
        if (is_readable($xcart_dir . $alt_skin_info['alt_skin_dir'] . XC_DS . $params['file'])) {
            $file = $alt_skin_info['alt_skin_dir'] . '/' . $params['file'];
        } else {
            $file = $smarty_skin_dir . '/' . $params['file'];
        }
    } else {
        $file = $smarty_skin_dir . '/' . $params['file'];
    }

    $type = $params['type'];
    $queue = isset($params['queue']) ? intval($params['queue']) : 0;
    $result = '';

    if (
        isset($config['General']['speedup_' . $type])
        && 'Y' == $config['General']['speedup_' . $type]
        && defined('AREA_TYPE')
        && 'C' == constant('AREA_TYPE')
    ) {
        if (
            !isset($params['direct_info']) 
            && !in_array($file, $already_included_files)
            && is_readable($xcart_dir . $file)
        ) {
            $deferRegistry[$type][$queue][$file] = $xcart_dir . $file;
            $already_included_files[] = $file;
        } elseif (isset($params['direct_info'])) {
            $fn = "func_get_direct_$type";
            $directInfoRegistry[$type][$queue][$file][] = $fn($params['direct_info']);
        } elseif (
            defined('DEVELOPMENT_MODE')
            && $type == 'js'
            && !is_readable($xcart_dir . $file)
        ) {
            assert('FALSE /*'.__FUNCTION__.' <b>load_defer is called with wrong file param file=</b>' . $xcart_dir . $file . '*/');
        }
    } else {
        if (!isset($params['direct_info'])) {
            if (!in_array($file, $already_included_files) && is_readable($xcart_dir . $file)) {
                $result = '';
                if ('js' == $type) {
                    $result = '<script type="text/javascript" src="' . $xcart_web_dir . $file . '"></script>';
                } elseif (
                    !empty($params['css_inc_mode']) 
                    && 'css' == $type
                ) {
                      // include css via @import directive: hack to get around IE limitation                                  
                      // applied to the number of css files    
                      $result = '@import url("' . $xcart_web_dir . $file . '"); ' . "\n";
                } else {
                    $result = '<link rel="stylesheet" type="text/css" href="' . $xcart_web_dir . $file . '" />';
                }
                
                $already_included_files[] = $file;

            }

        } else {

            $result = ('css' == $type)
                ? '<style type="text/css">' . "\n"
                  . '<!--' . "\n"
                  . func_get_direct_css($params['direct_info']) . "\n"
                  . '-->' . "\n"
                  . '</style>'
                : '<script type="text/javascript">' . "\n"
                  . '//<![CDATA[' . "\n"
                  . $params['direct_info'] . "\n"
                  . '//]]>' . "\n"
                  .'</script>';

        }

    }

    static $first_call = true;
    if (
        defined('DEVELOPMENT_MODE')
        && $first_call
    ) {
        $first_call = false;
        register_shutdown_function('func_check_load_defer_plugin_integrity');
    }

    return $result;
}

/**
 * return custom css styles
 *
 * @param array $directInfo custom CSS styles array
 *
 * @return string
 * @see    ____func_see____
 * @since  1.0.0
 */
function func_get_direct_js(&$directInfo) { // {{{
    return $directInfo;
} // }}}

function func_get_direct_css(&$directInfo) { // {{{

    $styles = array();

    if (!is_array($directInfo))
        return '';

    foreach ($directInfo as $id => $css) {
        $styles[$id] = $id . ' {';
        foreach ($css as $name => $value) {
            $styles[$id] .= $name . ': ' . $value . ';' . "\n";
        }

        $styles[$id] .= '}';
    }

    return implode("\n", $styles);
} // }}}

function func_check_load_defer_plugin_integrity()
{
    global $deferRegistry, $directInfoRegistry, $active_modules;

    if (isset($active_modules['XAuth']) && !$active_modules['XAuth']) {
        assert('empty($deferRegistry) && empty($directInfoRegistry) /*'.__FUNCTION__.' <b>It seems load_defer_code call should be added at the end of the page</b>*/');
    }
}
?>
