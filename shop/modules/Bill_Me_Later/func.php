<?php
/* vim: set ts=4 sw=4 sts=4 et: */
/*****************************************************************************\
+-----------------------------------------------------------------------------+
| X-Cart Software license agreement                                           |
| Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>            |
| All rights reserved.                                                        |
+-----------------------------------------------------------------------------+
| PLEASE READ THE FULL TEXT OF SOFTWARE LICENSE AGREEMENT IN THE "COPYRIGHT"  |
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
 * Bill Me Later module
 *
 * @category X-Cart
 * @package X-Cart
 * @subpackage Modules
 * @author Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license http://www.x-cart.com/license.php X-Cart license agreement
 * @version 2b39e63712da5477e1aaf5cfa80d1370f583bce9, v10 (xcart_4_7_0), 2015-02-17 23:56:28, func.php, Yuriy
 * @link http://www.x-cart.com/
 * @see ____file_see____
 */ 

if (!defined('XCART_START')) { 
    header('Location: ../../'); 
    die('Access denied'); 
}

function func_bml_on_module_toggle($module_name, $module_new_state) {

    if ($module_name != 'Bill_Me_Later') {
        return;
    }

    global $sql_tbl, $active_modules, $smarty;

    if ($module_new_state == TRUE) {

        x_load('paypal');

        $paypal_methods = "('" . implode("','", func_get_allowed_paypal_processors()). "')";

        $found_paypal = func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[ccprocessors] WHERE processor IN $paypal_methods AND paymentid <> 0");

        if (!$found_paypal) {

            db_query("UPDATE $sql_tbl[modules] SET active = 'N' WHERE module_name = '$module_name'");

            func_register_ajax_message(
                'moduleToggle',
                array(
                    'result' => 0,
                    'message' => array(
                        'content' => func_get_langvar_by_name('txt_bml_no_paypal', FALSE, FALSE, TRUE),
                        'type' => 'E'
                    )
                )
            );

            func_header_location('modules.php');

        } else {

            // Check if BML method not exists - then add it
            $bml_exist = func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[payment_methods] WHERE processor_file = 'ps_paypal_bml.php'");
            if (!$bml_exist) {
                $orderby = func_query_first_cell("SELECT orderby FROM $sql_tbl[payment_methods] WHERE processor_file = 'ps_paypal.php'");
                func_paypal_add_payment_methods($orderby, 'ps_paypal_bml.php');
            }

            // If any PayPal method is already active them BML should be active too
            $any_paypal_active = func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[payment_methods] WHERE processor_file IN $paypal_methods AND active = 'Y'");
            if ($any_paypal_active) {
                db_query("UPDATE $sql_tbl[payment_methods] SET active = 'Y' WHERE processor_file = 'ps_paypal_bml.php'");
            }

        }

    } else {

        db_query("UPDATE $sql_tbl[payment_methods] SET active = 'N' WHERE processor_file = 'ps_paypal_bml.php'");

    }

}

function func_bml_init() {

    global $current_area;

    if (defined('ADMIN_MODULES_CONTROLLER')) {
        func_add_event_listener('module.ajax.toggle', 'func_bml_on_module_toggle');
    }

    if (defined('AREA_TYPE') && AREA_TYPE == 'A') {
        func_add_event_listener('module.config.update', 'func_bml_on_config_update');
    }
}

function func_bml_get_publisherid($email) {

    global $config;

    $clientKey = '6e5ed40774ca7b82a7e5c61ec86533e3f1f65386';
    $sharedSecret = '034105372bfb99f86c5b5a6c5efc6df02349f0d9';
    $endPoint = 'https://api.financing.paypal.com/finapi/v1/publishers/';
    $bnCode = 'XCart_Cart';
    $timeStamp = XC_TIME . '000';

    x_load('http');

    $headers = array(
        'Authorization' => 'FPA ' . $clientKey . ':' . sha1($sharedSecret . $timeStamp) . ':' . $timeStamp,
        'Accept' => 'application/json',
    );

    $post = array(
        'sellerName' => $config['Company']['company_name'],
        'emailAddress' => $email,
        'bnCode' => $bnCode
    );

    $post = func_json_encode($post);

    list($a, $return) = func_https_request('POST', $endPoint, $post, '', '', 'application/json', '', '', '', $headers);

    if (defined('PAYPAL_DEBUG')){
        x_log_add('bml', "*** Headers:\n" . print_r($a, TRUE) . "\nResponse:\n" . print_r($return, TRUE));
    }

    $statusCode = 0;
    if (!empty($a)) {
        if (preg_match('/HTTP\/.*\s*([0-9][0-9][0-9])\s*/i', $a, $tmp)) {
            $statusCode = $tmp[1];
        }
    }

    $publisherId = '';

    if ($statusCode == 201 && !empty($return) && ($return = func_json_decode($return))) {
        $publisherId = $return->publisherId;
    }

    return $publisherId;

}

function func_bml_on_config_update($module_name, $new_config) {

    if ($module_name != 'Bill_Me_Later') {
        return;
    }

    if ($new_config['bml_enable_banners'] == 'Y') {

        global $config;

        if (empty($config['paypal_bml_publisherid']) || ($config['Bill_Me_Later']['bml_paypal_email'] != $new_config['bml_paypal_email'])) {
            $pubid = func_bml_get_publisherid($new_config['bml_paypal_email']);

            $config['paypal_bml_publisherid'] = $pubid;
            func_array2insert(
                'config',
                array(
                    'name'  => 'paypal_bml_publisherid',
                    'value' => $config['paypal_bml_publisherid'],
                ),
                TRUE
            );
        }

        if (empty($config['paypal_bml_publisherid'])) {
            // Can't obtain publisherId - disable banners
            func_array2update('config', array('value' => 'N'), "name='bml_enable_banners'");
            global $top_message;
            x_session_register('top_message');
            $top_message = array(
                'type' => 'E',
                'content' => func_get_langvar_by_name('txt_bml_no_pubid'),
            );

        }

    }

}

?>
