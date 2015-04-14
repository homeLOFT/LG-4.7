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
 * Common script to throw an error message
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v45 (xcart_4_7_0), 2015-02-17 23:56:28, error_message.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_SESSION_START') ) { header("Location: ../"); die("Access denied"); }

/**
 * Prepare the array of possible errors
 */
$possible_errors = array(
    'access_denied',
    'cant_open_file',
    'wrong_merchant_password',
    'realtime_shipping_disabled',
    'shipping_disabled',
    'error_ccprocessor_baddata',
    'last_admin',
    'product_disabled',
    'error_ccprocessor_error',
    'error_cmpi_error',
    'error_ccprocessor_unavailable',
    'disabled_cookies',
    'error_no_shipping',
    'subscribe_bad_email',
    'giftreg_is_private'
);

/**
 * Recognize the error
 */
if (empty($error)) {
    $error = preg_replace("/=$/", '', preg_replace('/&.*$/', '', $QUERY_STRING));
}

/**
 * Throw out a 404 page
 */
if (!in_array($error, $possible_errors)) {
    func_page_not_found();
    exit();
}

/**
 * Perform error-specific actions
 */

if ($error == 'disabled_cookies' && isset($ti)) {
    $save_data = func_db_tmpread(stripslashes($ti));
    $smarty->assign('save_data', func_htmlspecialchars($save_data));
    $smarty->assign('ti', $ti);
}

/**
 * Assign Smarty variables and show template
 */
$tmp = strstr($QUERY_STRING, $XCART_SESSION_NAME."=");
if (!empty($tmp))
    $QUERY_STRING = func_qs_remove($QUERY_STRING, $XCART_SESSION_NAME);

if ($current_area != 'C' && isset($id)) {
    $QUERY_STRING = func_qs_remove($QUERY_STRING, 'id');

    if (is_numeric($id) && $id > 0) {
        $message = func_get_langvar_by_name('txt_err_msg_code_' . $id, array(), false, true);

        if (empty($message))
            $message = func_get_langvar_by_name('txt_err_msg_code_X', array('code' => $id));

        x_log_add('INTERNAL', $message);
    }

    $smarty->assign('id', $id);
    $smarty->assign('message', $message);
}

$smarty->assign('main', $error);

/**
 * Assign login information
 */
x_session_register('login_antibot_on');
x_session_register('antibot_err');
x_session_register('username');

$smarty->assign('username', stripslashes($username));
$smarty->assign('login_antibot_on', $login_antibot_on);

if ($antibot_err) {
    $smarty->assign('antibot_err', $antibot_err);
    $antibot_err = false;
}

// Assign the current location line
$location[] = array(func_get_langvar_by_name('lbl_warning'), '');
$smarty->assign('location', $location);

?>
