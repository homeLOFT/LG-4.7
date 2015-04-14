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
 * Functions
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v8 (xcart_4_7_0), 2015-02-17 23:56:28, func.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header("Location: ../../"); die("Access denied"); }

abstract class XC_Simplify_Payment_Status {
    const APPROVED = 'APPROVED';
    const DECLINED = 'DECLINED';
}

abstract class XC_Simplify_Key_Type {
    const PUBLIC_KEY = 'public_key';
    const PRIVATE_KEY = 'private_key';
}

function func_simplify_init()
{

    if (!function_exists('SimplifySDK_loader')) {

        function SimplifySDK_loader($class_name) {
            global $xcart_dir;

            if (
                $class_name === 'Simplify'
                || strpos($class_name, 'Simplify_') === 0
            ) {
                include_once $xcart_dir . XC_DS . 'include/lib/simplifySDK/lib/Simplify.php';
            }
        }
    }

    if (defined('ADMIN_MODULES_CONTROLLER')) {
        // Register module toggle handler
        if (function_exists('func_add_event_listener')) {
            func_add_event_listener('module.ajax.toggle', 'func_simplify_on_module_toggle');
        }
    }

    if (defined('ADMIN_CONFIGURATION_CONTROLLER')) {
        // Register module config update handler
        if (function_exists('func_add_event_listener')) {
            func_add_event_listener('module.config.update', 'func_simplify_on_module_config_update');
        }
    }

    spl_autoload_register('SimplifySDK_loader');
}

function simplify_filter_simplify_config($options) {

    $payment_form_fields = array();
    $hosted_form_fields = array();

    foreach ($options as $option) {
        if (strstr($option['name'], '_hosted_') === false) {
            $payment_form_fields[] = $option;
        } else {
            $hosted_form_fields[] = $option;
        }
    }

    return array($payment_form_fields, $hosted_form_fields);
}

function func_simplify_get_ccprocessor_data($integration_type)
{
    $result = array();

    // Prepare data to insert to DB
    $db_data = array(
        XC_SIMPLIFY_CC => array(
            'module_name'       => 'Simplify Commerce by MasterCard',
            'type'              => 'D',
            'processor'         => XC_SIMPLIFY_CC,
            'template'          => 'cc_simplify.tpl',
            'param01'           => '',
            'param02'           => '',
            'param03'           => '',
            'param04'           => '',
            'param05'           => '',
            'param06'           => '',
            'param07'           => '',
            'param08'           => '',
            'param09'           => '',
            'disable_ccinfo'    => 'Y',
            'background'        => 'Y',
            'testmode'          => 'Y',
            'is_refund'         => '',
            'c_template'        => 'modules/Simplify/payment_form.tpl',
            'has_preauth'       => '',
            'preauth_expire'    => 0,
            'capture_min_limit' => '0%',
            'capture_max_limit' => '0%',
        ),
        XC_SIMPLIFY_CC_HOSTED => array(
            'module_name'       => 'Simplify Commerce by MasterCard - Hosted Payments',
            'type'              => 'D',
            'processor'         => XC_SIMPLIFY_CC_HOSTED,
            'template'          => 'cc_simplify_hosted.tpl',
            'param01'           => '',
            'param02'           => '',
            'param03'           => '',
            'param04'           => '',
            'param05'           => '',
            'param06'           => '',
            'param07'           => '',
            'param08'           => '',
            'param09'           => '',
            'disable_ccinfo'    => 'Y',
            'background'        => 'N',
            'testmode'          => 'Y',
            'is_refund'         => '',
            'c_template'        => '',
            'has_preauth'       => '',
            'preauth_expire'    => 0,
            'capture_min_limit' => '0%',
            'capture_max_limit' => '0%',
        )
    );

    if (isset($db_data[$integration_type])) {
        $result = $db_data[$integration_type];
    }

    return $result;
}

function func_simplify_on_module_enable()
{
    x_load('payment');

    // Add processors data to DB
    func_array2insert(
        'ccprocessors',
        func_simplify_get_ccprocessor_data(XC_SIMPLIFY_CC)
    );
    func_array2insert(
        'ccprocessors',
        func_simplify_get_ccprocessor_data(XC_SIMPLIFY_CC_HOSTED)
    );

    // Register processors
    func_add_processor(XC_SIMPLIFY_CC);
    func_add_processor(XC_SIMPLIFY_CC_HOSTED);
}

function func_simplify_on_module_disable()
{
    global $sql_tbl;

    x_load('payment');

    // Unregister processor
    func_remove_processor(XC_SIMPLIFY_CC);
    func_remove_processor(XC_SIMPLIFY_CC_HOSTED);

    // Remove processor from DB
    db_query("DELETE FROM $sql_tbl[ccprocessors] WHERE processor='" . XC_SIMPLIFY_CC . "'");
    db_query("DELETE FROM $sql_tbl[ccprocessors] WHERE processor='" . XC_SIMPLIFY_CC_HOSTED . "'");
}

function func_simplify_on_module_toggle($module_name, $module_new_state)
{
    $redirect = '';

    if ($module_name === XC_SIMPLIFY) {
        switch ($module_new_state) {
            case true:
                    func_simplify_on_module_enable($module_name);
                break;
            case false:
                    func_simplify_on_module_disable($module_name);
                break;
        }
    }

    return $redirect;
}

function func_simplify_on_module_config_update($option, $section_data)
{
    if (!empty($section_data['simplify_testmode'])) {
        // Update processor data in DB
        func_array2update(
            'ccprocessors',
            array (
                'testmode' => $section_data['simplify_testmode']
            ),
            "processor='" . XC_SIMPLIFY_CC . "'"
        );
    }
    if (!empty($section_data['simplify_hosted_testmode'])) {
        // Update processor data in DB
        func_array2update(
            'ccprocessors',
            array (
                'testmode' => $section_data['simplify_testmode']
            ),
            "processor='" . XC_SIMPLIFY_CC_HOSTED . "'"
        );
    }
}

function func_simplify_get_payment_id()
{
    x_load('payment');

    $tmp = func_get_processor_by_id(XC_SIMPLIFY_CC);

    if ($tmp['paymentid']) {
        return $tmp['paymentid'];
    }

    return false;
}

function func_simplify_get_pm_params($type = XC_SIMPLIFY_CC)
{
    global $config;

    static $simplify_module_params = array();

    if (empty($simplify_module_params[$type])) {
        // Load payment functions
        x_load('payment');
        // Get gateway params
        $simplify_module_params[$type] = func_get_pm_params($type);
        // Module params
        $simplify_module_params[$type] = array_merge($simplify_module_params[$type], $config[XC_SIMPLIFY]);

        $modifier = '';

        switch ($type) {
            case XC_SIMPLIFY_CC:
                $modifier = '';
                break;
            case XC_SIMPLIFY_CC_HOSTED:
                $modifier = '_hosted';
                break;
        }

        // get current mode
        $mode = $config[XC_SIMPLIFY]['simplify' . $modifier . '_testmode'] == 'Y' ? 'test' : 'live';
        // Set current params
        $simplify_module_params[$type][XC_Simplify_Key_Type::PUBLIC_KEY] = $config[XC_SIMPLIFY]['simplify' . $modifier . '_' . $mode . '_' . XC_Simplify_Key_Type::PUBLIC_KEY];
        $simplify_module_params[$type][XC_Simplify_Key_Type::PRIVATE_KEY] = $config[XC_SIMPLIFY]['simplify' . $modifier . '_' . $mode . '_' . XC_Simplify_Key_Type::PRIVATE_KEY];
    }

    if (!empty($simplify_module_params[$type])) {
        // Gateway is configured and has required params
        return $simplify_module_params[$type];
    }

    return false;
}

function func_simplify_hosted_check_signature($request, $privateKey)
{
    $signature = $request['signature'];

    $recreatedSignature = strtoupper(md5(
                    $request['amount']
                    . $request['reference']
                    . $request['paymentId']
                    . $request['paymentDate']
                    . $request['paymentStatus']
                    . $privateKey));

    return $recreatedSignature == $signature;
}

?>
