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
 * Debug functions
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v37 (xcart_4_7_0), 2015-02-17 23:56:28, func.debug.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header("Location: ../"); die("Access denied"); }

if (!defined('DEVELOPMENT_MODE')) {
    return FALSE;
}

x_load('files');

/**
 * For testing purpose: outputs contents of requested variables
 * example:
 *  func_print_r($categories, $cart, $userinfo, $GLOBALS);
 */
function func_print_r() { // {{{
    static $count = 0;
    global $login;

    $args = func_get_args();

    $msg = '<div align="left"><pre><font>';
    $log = "Logged as: $login\n";
    if (!empty($args)) {
        foreach ($args as $index => $variable_content) {
            $msg .= "<b>Debug [$index/$count]:</b> ";
            $log .= "Debug [$index/$count]: ";
            $data = print_r($variable_content, TRUE);
            $data .= "\n";
            $msg .= func_htmlspecialchars($data);
            $log .= $data;
        }
    } else {
        $msg .= '<b>Debug notice:</b> try to use func_print_r($varname1,$varname2); '."\n";
        $log .= 'Debug notice: try to use func_print_r($varname1,$varname2); '."\n";
    }

    $msg .= "</font></pre></div>";

    if (x_debug_ctl('P') === TRUE) {
        echo $msg;
    }

    x_log_flag('log_debug_messages', 'DEBUG', $log, TRUE, 1);

    $count++;
} // }}}

/**
 * For testing purpose: outputs contents of requested global variables
 * example:
 *   func_print_d();
 *   func_print_d('categories', 'cart', 'userinfo');
 */
function func_print_d() { // {{{
    global $login;

    $varnames = func_get_args();
    if (empty($varnames)) {
        $varnames[] = "GLOBALS";
    }

    $msg = '<div align="left"><pre><font>';
    $log = "Logged as: $login\n";
    foreach ($varnames as $variable_name) {
        if (!is_string($variable_name) || empty($variable_name)) {
            $msg .= '<b>Debug notice:</b> try to use func_print_d("varname1","varname2") instead of func_print_d($varname1,$varname2); '."\n";
            $log .= 'Debug notice: try to use func_print_d("varname1","varname2") instead of func_print_d($varname1,$varname2); '."\n";
        } else {
            $msg .= "<b>$variable_name</b> = ";
            $log .= "$variable_name = ";
            if ($variable_name == 'GLOBALS') {
                $data = print_r($GLOBALS, TRUE);
            } elseif (!isset($GLOBALS[$variable_name])) {
                $data = "NULL";
            } else {
                $data = print_r($GLOBALS[$variable_name], TRUE);
            }

            $data .= "\n";
            $msg .= func_htmlspecialchars($data);
            $log .= $data;
        }
    }

    $msg .= "</font></pre></div>";

    if (x_debug_ctl('P') === TRUE) {
        echo $msg;
    }

    x_log_flag('log_debug_messages', 'DEBUG', $log, TRUE, 1);
} // }}}

/**
 * For testing purpose: outputs contents using format string like sprintf() does
 * example:
 *   func_print_f("var1=%f, var2=%f, array3=%s",$var1,$var2,$array3);
 */
function func_print_f() { // {{{
    global $login;
    global $xcart_dir;

    $args = func_get_args();
    assert('count($args) > 1 && is_string($args[0]) /* '.__FUNCTION__.': incorrect arguments */');

    foreach ($args as $k => $v) {
        if (!is_scalar($v)) {
            $args[$k] = print_r($v, TRUE);
        }
    }

    $bt = func_get_backtrace(1);
    $suffix = $bt[0];
    if (func_pathcmp($suffix, $xcart_dir.XC_DS, 2)) {
        $suffix = substr($suffix, strlen($xcart_dir)+1);
    }

    $suffix = ' ('.$suffix.')';

    $str = call_user_func_array('sprintf', $args);
    if (strlen($str) < 1) $str = '(empty debug message)';

    $log = "Logged as: $login\nDebug: ".$str."\n";

    $msg = '<div align="left"><pre><font>';
    $msg .= "<b>Debug:</b> ".func_htmlspecialchars($str.$suffix)."\n";
    $msg .= "</font></pre></div>\n";

    if (x_debug_ctl('P') === TRUE) {
        echo $msg;
    }

    x_log_flag('log_debug_messages', 'DEBUG', $log, TRUE, 1);
} // }}}

/**
 * This function displays how much memory currently is used
 */
function func_get_memory_used($label = "") { // {{{
    $backtrace = debug_backtrace();
    echo "$label File: " . $backtrace[0]['file'] . "<br />Line: " . $backtrace[0]['line'] . "<br />Memory is used: " . memory_get_usage() . "<hr />";
} // }}}


function func_assert_failure_handler($file, $line, $code)
{//{{{
    global $xcart_dir, $config;

    $db_full = debug_backtrace(!DEBUG_BACKTRACE_PROVIDE_OBJECT);
    if (
        strpos($code, 'showTopMessage') !== false
        || strpos($code, 'SHORT_BACKTRACE') !== false
    ) {
        $db = array_slice($db_full, 0, 3);
    } else {
        $db = $db_full;
    }
    $filename = x_log_add('Assertion', $db);

    if (!empty($filename))
        $filename = str_replace($xcart_dir . '/', '', $filename);

    echo "<hr>Assertion Failed:
        File '$file':Line '$line'<br />
        Code '$code'<br />Please post a new ticket here <a href='https://bt.x-cart.com/bug_report_page.php?product_version=$config[version]'>https://bt.x-cart.com</a><br />Please, attach the $filename file to the ticket.<hr />.";

    if (strpos($code, '(EE)') !== FALSE) {
        // This is critical assert
        die;
    }
}//}}}

/**
 * Validate php constant values
 */
function func_dev_check_logical_errors() { // {{{
    if (defined('SKIP_CHECK_REQUIREMENTS.PHP'))
        assert('constant("SKIP_CHECK_REQUIREMENTS.PHP") === true /*Unexpected usage of SKIP_CHECK_REQUIREMENTS.PHP constant.Possible values are true and undefined*/');

    if (defined('SKIP_ALL_MODULES'))
        assert('constant("SKIP_ALL_MODULES") === true /*Unexpected usage of SKIP_ALL_MODULES constant.Possible values are true and undefined*/');

    if (defined('QUICK_START'))
        assert('constant("QUICK_START") === true /*Unexpected usage of QUICK_START constant.Possible values are true and undefined*/');

    if (defined('XC_DISABLE_SESSION_SAVE'))
        assert('constant("XC_DISABLE_SESSION_SAVE") === true /*Unexpected usage of XC_DISABLE_SESSION_SAVE constant.Possible values are true and undefined*/');

    if (defined('USE_SIMPLE_DB_INTERFACE'))
        assert('constant("USE_SIMPLE_DB_INTERFACE") === true /*Unexpected usage of USE_SIMPLE_DB_INTERFACE constant.Possible values are true and undefined*/');

    if (defined('DO_NOT_START_SESSION'))
        assert('constant("DO_NOT_START_SESSION") === 1 /*Unexpected usage of DO_NOT_START_SESSION constant.Possible values are 1 and undefined*/');

    if (defined('IS_XPC_IFRAME'))
        assert('constant("IS_XPC_IFRAME") === 1 /*Unexpected usage of IS_XPC_IFRAME constant.Possible values are 1 and undefined*/');

    if (defined('ANTIBOT_SKIP_INIT'))
        assert('constant("ANTIBOT_SKIP_INIT") === true /*Unexpected usage of ANTIBOT_SKIP_INIT constant.Possible values are true and undefined*/');

    if (defined('USE_DATA_CACHE'))
        assert('is_bool(constant("USE_DATA_CACHE")) /*Unexpected usage of USE_DATA_CACHE constant.Possible values are true/false and undefined*/');

    if (defined('USE_SESSION_HISTORY'))
        assert('constant("USE_SESSION_HISTORY") === true /*Unexpected usage of USE_SESSION_HISTORY constant.Possible values are true and undefined*/');

    if (defined('ADMIN_MODULES_CONTROLLER'))
        assert('constant("ADMIN_MODULES_CONTROLLER") === true /*Unexpected usage of ADMIN_MODULES_CONTROLLER constant.Possible values are true and undefined*/');

} // }}}


/**
 * https://php.net/manual/ru/function.array-diff-assoc.php#111675
 */
function func_array_diff_assoc_recursive($array1, $array2) {//{{{
    $difference=array();
    foreach($array1 as $key => $value) {
        if( is_array($value) ) {
            if( !isset($array2[$key]) || !is_array($array2[$key]) ) {
                $difference[$key] = $value;
            } else {
                $new_diff = func_array_diff_assoc_recursive($value, $array2[$key]);
                if( !empty($new_diff) )
                    $difference[$key] = $new_diff;
            }
        } else if( !array_key_exists($key,$array2) || $array2[$key] !== $value ) {
            $difference[$key] = $value;
        }
    }
    return $difference;
}//}}}

/**
 * Find not saved session variables
 */
function func_dev_check_non_saved_session_vars() {//{{{
    global $XCARTSESSID, $XCART_SESSION_VARS, $XCART_SESSION_UNPACKED_VARS, $sql_tbl;

    $old_data = func_query_first_cell("SELECT data FROM $sql_tbl[sessions_data] WHERE sessid = '$XCARTSESSID'");
    if (!empty($old_data)) {
        $old_data = x_session_unserialize($old_data);
    } else {
        $old_data = array();
    }

    $global_session_data = array();
    foreach (array_keys($XCART_SESSION_VARS) as $varname) {
        if (isset($GLOBALS[$varname])) {
            $global_session_data[$varname] = $GLOBALS[$varname];
        }
    }

    //$absent_globals = array_diff_assoc($old_data, $global_session_data); //not major array
    $absent_globals = $session_data = array();
    $not_saved_globals = func_array_diff_assoc_recursive($global_session_data, $old_data);

    foreach ($not_saved_globals as $varname => $value) {
        if ($XCART_SESSION_VARS[$varname] === $GLOBALS[$varname]) {
            unset($not_saved_globals[$varname]);
        } else {
            $session_data[$varname] = $XCART_SESSION_VARS[$varname];
        }
    }

    assert('!empty($old_data) && empty($not_saved_globals) /*'.__FUNCTION__.' Some session vars were not saved via x_session_save()' . "\n\n<br /><br />sessions ". print_r($session_data, true) . "\n<br />---------------------\n<br />globals " . print_r($not_saved_globals, true) . '*/');
}//}}}

?>
