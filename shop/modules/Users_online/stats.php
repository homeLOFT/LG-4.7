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
 * Users online statistics
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v39 (xcart_4_7_0), 2015-02-17 23:56:28, stats.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_SESSION_START') ) { header("Location: ../"); die("Access denied"); }

x_load('user');

$sesses = func_query("SELECT sessid, is_registered, expiry FROM $sql_tbl[users_online] WHERE usertype = 'C'");

$statistics = array();

if (empty($sesses))
    return;

foreach ($sesses as $s) {

    $data = func_query_first("SELECT data, expiry, start FROM $sql_tbl[sessions_data] WHERE sessid = '$s[sessid]'");

    if (empty($data))
        continue;

    $expiry_session = $data['expiry'];
    $session_create_date = $data['start'];
    $data = $data['data'];
    $data = unserialize($data);

    if (
        empty($data['current_url_page'])
        || (
            !empty($data['login'])
            && $data['login_type'] != 'C'
        )
    ) {
        continue;
    }

    $rec = array(
        'last_date' => $s['expiry'],
    );

    if (!empty($data['login'])) {

        $rec['userinfo'] = func_userinfo($data['logged_userid'], 'C');

    }

    if (!empty($data['cart']['products'])) {

        $rec['products'] = $data['cart']['products'];

    }

    // Calculate current session time based on sessions_data.expiry
    $_current_date = $expiry_session - XCART_SESSION_LENGTH;
    if (
        !empty($active_modules['POS_System'])
        && $_current_date - XC_POS_OPERATOR_SESSION_LENGTH > $session_create_date
    ) {
        // It seems this is the POS operator @see func_get_pos_operator_session_duration
        $_current_date -= XC_POS_OPERATOR_SESSION_LENGTH;
    }
    $_current_date += $config['Appearance']['timezone_offset'];
    $_current_date = max($_current_date, $session_create_date);

    $rec['current_date'] = $_current_date;

    $rec['current_url_page'] = $data['current_url_page'];

    $rec['display_url_page'] = (strstr($data['current_url_page'], $https_location))
        ? str_replace('https://' . $xcart_https_host, '', $data['current_url_page'])
        : str_replace('http://' . $xcart_http_host, '', $data['current_url_page']);

    $rec['session_create_date'] = $session_create_date + $config["Appearance"]["timezone_offset"];

    $statistics[] = $rec;

} // foreach ($sesses as $s)

?>
