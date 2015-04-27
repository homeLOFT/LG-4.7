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
 * HTTP-HTTPS redirection mechanism code
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Customer interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v81 (xcart_4_7_0), 2015-02-17 23:56:28, https.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header("Location: home.php"); die("Access denied"); }

x_load('files');

x_session_register('https_redirect_counter', 0);

x_session_register('https_redirect_forbidden', false);

$https_messages = array(
    array(
        "mode=order_message",
        "mode=order_message_widget",
        "orderids="
    ),
    'error_message.php'
);

$https_scripts = array();

$_dir_user = func_get_area_catalog(AREA_TYPE, true);

if ($config['Security']['use_https_login'] == 'Y') {

    $https_scripts[] = 'register.php';
    $https_scripts[] = 'change_password.php';
    $https_scripts[] = 'login.php';
    $https_scripts[] = array(
        'cart.php',
        "mode=checkout",
    );
    $https_scripts[] = array(
        'cart.php',
        "mode=auth",
    );
    $https_scripts[] = array(
        'help.php', 
        "section=contactus"
    );
    $https_scripts[] = array(
        'help.php',
        "section=Password_Recovery"
    );
    $https_scripts[] = 'pplogin_return.php';

    // Login form on the home page
    if (
        $current_area != 'C'
        && empty($login)
    ) {
        $https_scripts[] = 'home.php';
    }

    if ($current_area != 'A') {

        // Add payment scripts entries to $https_scripts
        $processor_files = func_query_column("SELECT DISTINCT processor_file FROM $sql_tbl[payment_methods] WHERE processor_file != '' AND active = 'Y'");
        if (!empty($processor_files)) {
            $https_scripts = array_merge($https_scripts, $processor_files);
        }
        unset($processor_files);

    }

} else if (!empty($active_modules['XPayments_Connector'])) {

    if (
        $current_area != 'A'
        && func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[payment_methods] WHERE processor_file = 'cc_xpc.php' AND active = 'Y'")
    ) {

        // Check if site is available via HTTPS if it has not been checked yet

        if (empty($config['HTTPS_test_passed'])) {
            x_load('tests');
            test_https_availibility();
        }

        // Force HTTPS on checkout if X-Payments payment method(s) are enabled and HTTPS check passed OK

        if ($config['XPayments_Connector']['xpc_use_iframe'] == 'Y' && $config['HTTPS_test_passed'] == 'Y') {

            $https_scripts[] = 'cc_xpc.php';
            $https_scripts[] = array(
                'cart.php',
                "mode=checkout",
            );
        }

        if ($config['HTTPS_test_passed'] == 'Y') {
             $https_scripts[] = 'saved_cards.php';
        }

    }

}

if (!function_exists('is_https_link')) {
function is_https_link($link, $https_scripts) {
return true;

    /*
     Corect possible bugs in func_is_always_allowed_link also
    */
    if (empty($https_scripts))
        return false;

    $link = preg_replace('!^/+!S', '', $link);

    foreach ($https_scripts as $https_script) {

        if (!is_array($https_script))
            $https_script = array($https_script);

        $tmp = true;

        foreach ($https_script as $v) {

            $p = strpos($link, $v);

            if ($p === false) {
                $tmp = false;
                break;
            }

            if ($v[strlen($v)-1] === '=') continue;

            if ($p + strlen($v) < strlen($link)) {

                $last = $link[$p+strlen($v)];

                if ($last === '?') continue;

                if ($last !== '&') {

                    $tmp = false;

                    break;
                }

            }

        }

        if ($tmp) return true;
    }

    return false;
}
}

$current_script = '/' . basename($PHP_SELF . ($QUERY_STRING ? "?$QUERY_STRING" : ''));

/**
 * Generate additional PHPSESSID var
 */
$additional_query = ($QUERY_STRING ? "&" : "?")
    . (
        strstr($QUERY_STRING, $XCART_SESSION_NAME)
        ? ''
        : $XCART_SESSION_NAME . "=" . $XCARTSESSID
    );

if (
    !preg_match("/(?:^|&)sl=/", $additional_query)
    && $xcart_http_host != $xcart_https_host
) {
    // $store_language is variable from customer area. Add code to avoid PHP notice
    if (
        empty($store_language)
        && in_array($current_area, array('A', 'P'))
    ) {
        $store_language = '';
    } else {
        assert('!empty($store_language) /* empty $store_language */');
    }

    $additional_query .= "&sl=" . $store_language . "&is_https_redirect=Y";
}

if (TRUE 
    && !defined('X_CRON')
    && !empty($REQUEST_METHOD)
    && $REQUEST_METHOD == 'GET'
    && empty($_GET['keep_https'])
    && ($HTTPS || !$https_redirect_forbidden)
) {
    $tmp_location = '';

    if (
        !$HTTPS
        && is_https_link($current_script, $https_scripts)
    ) {

        $tmp_location = $_dir_user . $current_script . $additional_query;

    } elseif (
        !$HTTPS
        && is_https_link($current_script, $https_messages)
        && !strncasecmp($HTTP_REFERER, $https_location, strlen($https_location))
    ) {

        $tmp_location = $_dir_user . $current_script . $additional_query;

    } elseif (
        $config['Security']['leave_https'] == 'Y'
        && $HTTPS
        && !is_https_link($current_script, $https_scripts)
        && !is_https_link($current_script, $https_messages)
        && !func_is_ajax_request()
        && !in_array(AREA_TYPE, array('A', 'P'))
    ) {

        x_session_register('login_redirect');

        $do_redirect = empty($login_redirect);

        x_session_unregister('login_redirect');

        if ($do_redirect) {

            $_dir_user = func_get_area_catalog(AREA_TYPE, false);

            $tmp_location = $_dir_user . $current_script . $additional_query;

        }

    }

    $https_redirect_limit = intval($https_redirect_limit);

    if (
        !empty($tmp_location)
        && !$HTTPS
        && $https_redirect_limit > 0
        && $https_redirect_counter > $https_redirect_limit
    ) {
        $https_redirect_forbidden = true;
    }

    if (
        !empty($tmp_location)
        && (
            $HTTPS
            || !$https_redirect_forbidden
        )
    ) {

        $https_redirect_counter++;

        if ($smarty->webmaster_mode) {
            echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<body>
<script type="text/javascript">
//<![CDATA[
var _smarty_console = window.open("","console","width=360,height=500,resizable,scrollbars=yes");
if (_smarty_console)
    _smarty_console.close();
//]]>
</script>';
            echo "<br /><br />".func_get_langvar_by_name('txt_header_location_note', array('time' => 2, 'location' => $tmp_location), false, true, true);
            echo "<meta http-equiv=\"Refresh\" content=\"0;URL=$tmp_location\" />";
            echo "</body>\n</html>";

            exit;

        } else {

            func_header_location($tmp_location, TRUE, 301);

        }

    } else {

        $https_redirect_counter = 0;

    }
}

?>
