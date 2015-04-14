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
 * Payment processor configuration interface
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Admin interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v166 (xcart_4_7_0), 2015-02-17 13:29:01, cc_processing.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */
// For explanation of cc processing please refer to
// X-Cart developer's documentation

require __DIR__.'/auth.php';
require $xcart_dir . '/include/security.php';

x_load(
    'backoffice',
    'crypt',
    'tests',
    'paypal',
    'payment'
);

x_session_register('recent_payment_methods', array());

if (
    $mode == 'add'
    && !empty($processor)
) {

    require $xcart_dir . '/include/safe_mode.php';

    func_add_processor($processor);

    func_header_location('payment_methods.php#payment-tabs-payment-methods');

}

if (
    $mode == 'delete'
    && $paymentid
) {

    require $xcart_dir . '/include/safe_mode.php';

    $tmp = func_query_first("SELECT $sql_tbl[ccprocessors].paymentid, $sql_tbl[ccprocessors].processor FROM $sql_tbl[ccprocessors], $sql_tbl[payment_methods] WHERE $sql_tbl[payment_methods].paymentid = '".$paymentid."' AND ($sql_tbl[ccprocessors].paymentid = $sql_tbl[payment_methods].paymentid OR $sql_tbl[ccprocessors].processor = $sql_tbl[payment_methods].processor_file)");

    if (!empty($tmp)) {

        if (func_is_paypal_processor($tmp['processor'])) {

            func_paypal_remove_payment_methods();

            if (is_array($recent_payment_methods)) {

                foreach ($recent_payment_methods as $k => $v) {
                    if (func_is_paypal_processor($v['script'])) {
                        unset($recent_payment_methods[$k]);
                    }
                }

            }

        } else {

            db_query("DELETE FROM $sql_tbl[payment_methods] WHERE paymentid='" . $paymentid . "'");

            db_query("UPDATE $sql_tbl[ccprocessors] SET paymentid='0' where paymentid='" . $paymentid . "'");

        }

        if (isset($recent_payment_methods[$tmp['processor'] . $paymentid])) {

            unset($recent_payment_methods[$tmp['processor'] . $paymentid]);

        }

    }

    func_header_location('payment_methods.php');
}

/**
 * Setup paramxx in ccprocessors table
 */
if (
    $REQUEST_METHOD == 'POST'
    && empty($mode)
) {

    require $xcart_dir . '/include/safe_mode.php';

    if (!empty($cc_processor)) {

        if (!empty($active_modules['Klarna_Payments'])) {
            func_klarna_update_compaigns_and_redirect($cc_processor);
        }
        
        $top_message = array(
            'type'         => 'I',
            'content'     => func_get_langvar_by_name('msg_adm_payment_method_upd'),
        );

        if (func_is_paypal_processor($cc_processor)) {

            $map = array (
                'ipn' => 'ps_paypal.php',
            );

            if (!in_array($paypal_solution, array('ipn','pro','uk','express','advanced','payflowlink','pro_hosted')))
                $paypal_solution = 'ipn';

            if (
                $config['paypal_solution'] == 'pro'
                || $config['paypal_solution'] == 'express'
            ) {

                func_array2insert(
                    'config',
                    array(
                        'name'  => 'paypal_last_pro_solution',
                        'value' => 'pro',
                    ),
                    true
                );

            } elseif ($config['paypal_solution'] == 'uk') {

                func_array2insert(
                    'config',
                    array(
                        'name'  => 'paypal_last_pro_solution',
                        'value' => 'uk',
                    ),
                    true
                );
            } elseif ($config['paypal_solution'] == 'advanced') {

                func_array2insert(
                    'config',
                    array(
                        'name'  => 'paypal_last_pro_solution',
                        'value' => 'advanced',
                    ),
                    true
                );

                // update testmode for express checkout as well
                func_array2update(
                    'ccprocessors',
                    array(
                        'testmode' => $conf_data['advanced']['testmode'],
                    ),
                    "processor = 'ps_paypal_pro.php'"
                );
            } elseif ($config['paypal_solution'] == 'payflowlink') {

                func_array2insert(
                    'config',
                    array(
                        'name'  => 'paypal_last_pro_solution',
                        'value' => 'payflowlink',
                    ),
                    true
                );
            } elseif ($config['paypal_solution'] == 'pro_hosted') {

                func_array2insert(
                    'config',
                    array(
                        'name'  => 'paypal_last_pro_solution',
                        'value' => 'pro_hosted',
                    ),
                    true
                );
            }

            if ($paypal_solution == 'pro') {

                $map['pro'] = 'ps_paypal_pro.php';

            } else if ($paypal_solution == 'pro_hosted') {

                $map['pro_hosted'] = 'ps_paypal_pro_hosted.php';

            } elseif ($paypal_solution == 'advanced') {

                $_POST['conf_data']['payflowlink'] = $_POST['conf_data']['advanced'];
                $map['payflowlink'] = 'ps_paypal_payflowlink.php';

                $map['advanced'] = 'ps_paypal_advanced.php';

            } elseif ($paypal_solution == 'payflowlink') {

                $_POST['conf_data']['advanced'] = $_POST['conf_data']['payflowlink'];
                $map['advanced'] = 'ps_paypal_advanced.php';

                $map['payflowlink'] = 'ps_paypal_payflowlink.php';

            } elseif ($paypal_solution == 'express') {

                $map['express'] = 'ps_paypal_pro.php';

            } elseif ($paypal_solution == 'uk') {

                $map['uk'] = 'ps_paypal_pro.php';

            } elseif (
                $paypal_solution == 'ipn'
                && !empty($config['paypal_last_pro_solution'])
            ) {

                $map[$config['paypal_last_pro_solution']] = 'ps_paypal_pro.php';

            }

            func_array2insert(
                'config',
                array(
                    'name'  => 'paypal_solution',
                    'value' => $paypal_solution,
                ),
                true
            );

            if ($paypal_solution == 'express') {

                func_array2insert(
                    'config',
                    array(
                        'name'  => 'paypal_express_method',
                        'value' => $paypal_express_method,
                    ),
                    true
                );

                if (!empty($paypal_express_email)) {

                    func_array2insert(
                        'config',
                        array(
                            'name'  => 'paypal_express_email',
                            'value' => $paypal_express_email,
                        ),
                        true
                    );

                }

            }

            if ($paypal_solution == 'ipn') {

                func_array2insert(
                    'config',
                    array(
                        'name'  => 'paypal_address_override',
                        'value' => isset($_POST['paypal_address_override']) ? $_POST['paypal_address_override'] : 'N',
                    ),
                    true
                );
            }    

            $enable_paypal = (
                $paypal_solution != $config['paypal_solution']
                && (
                    $paypal_solution == 'ipn'
                    || $config['paypal_solution'] == 'ipn'
                    || $paypal_solution == 'advanced'
                    || $paypal_solution == 'payflowlink'
                    || $paypal_solution == 'pro_hosted'
                )
            );

            func_disable_paypal_methods($paypal_solution, $enable_paypal);

            // set params
            foreach ($map as $map_key => $processor) {

                if (!empty($_POST['conf_data'][$map_key])) {

                    if (
                        !empty($active_modules['XPayments_Connector'])
                        && isset($_POST['conf_data'][$map_key]['use_xpc'])
                        && isset($_POST['conf_data'][$map_key]['use_xpc_processor'])
                    ) {

                        func_array2insert(
                            'config',
                            array(
                                'name'  => 'paypal_dp_use_xpc_' . $map_key,
                                'value' => $_POST['conf_data'][$map_key]['use_xpc'],
                            ),
                            true
                        );

                        func_array2insert(
                            'config',
                            array(
                                'name'  => 'paypal_dp_use_xpc_processor_' . $map_key,
                                'value' => $_POST['conf_data'][$map_key]['use_xpc_processor'],
                            ),
                            true
                        );

                        unset($_POST['conf_data'][$map_key]['use_xpc'], $_POST['conf_data'][$map_key]['use_xpc_processor']);

                    }

                    if (
                        !empty($_POST['conf_data'][$map_key]['params'])
                        && is_array($_POST['conf_data'][$map_key]['params'])
                    ) {

                        db_query("DELETE FROM $sql_tbl[ccprocessor_params] WHERE processor='$processor'");

                        foreach ($_POST['conf_data'][$map_key]['params'] as $_param => $_value) {

                            func_array2insert(
                                'ccprocessor_params',
                                array(
                                    'processor' => $processor,
                                    'param' => $_param,
                                    'value' => $_value
                                )
                            );

                        }

                        unset($_POST['conf_data'][$map_key]['params']);

                    }



                    func_array2update(
                        'ccprocessors',
                        $_POST['conf_data'][$map_key],
                        "processor = '$processor'"
                    );


                }

            }

            $cc_processor = $processor;

        } else {

            if (
                stristr($cc_processor, 'cc_anz')
                && !isset($_POST['param05'])
            ) {
                $_POST['param05'] = '';
            }

            if (
                $cc_processor == 'cc_2conew.php'
                && !isset($_POST['param04'])
            ) {
                $_POST['param04'] = 'N';
            }

            if ($cc_processor == 'cc_csrc_sa_wm.php') {

                func_array2insert(
                    'ccprocessor_params',
                    array(
                        'processor' => $cc_processor,
                        'param' => 'access_key',
                        'value' => $access_key
                    ),
                    true
                );
                func_array2insert(
                    'ccprocessor_params',
                    array(
                        'processor' => $cc_processor,
                        'param' => 'secret_key',
                        'value' => $secret_key
                    ),
                    true
                );

                unset($_POST['access_key']);
                unset($_POST['secret_key']);
            }

            if ($cc_processor == 'cc_netbanx.php') {
                $_POST['param05'] = is_array($_POST['param05'])
                    ? serialize($_POST['param05'])
                    : '';
            }


            foreach($_POST as $key => $value) {

                if ($key == $XCART_SESSION_NAME) continue;

                if (
                    $cc_processor == 'ch_authorizenet.php'
                    && (
                        $key == 'param01'
                        || $key == 'param02'
                    )
                ) {
                    $value = text_crypt($value);
                }

                func_array2update(
                    'ccprocessors',
                    array(
                        $key => addslashes($value),
                    ),
                    "processor='" . $cc_processor . "'"
                );

            }

        }

    } // if (!empty($cc_processor))

    func_header_location("cc_processing.php?mode=update&cc_processor=$cc_processor");

} elseif ($REQUEST_METHOD == 'POST' && $mode == 'update_country') {

    $opt_name = 'paypal_country';
    $config[$opt_name] = $paypal_country;

    func_array2insert(
        'config',
        array(
            'name'  => $opt_name,
            'value' => $config[$opt_name],
        ),
        TRUE
    );

    if (!empty($config['paypal_solution']) && $config['paypal_country'] != 'none') {

        $show_paypal_methods = func_check_payment_country('ps_paypal.php', $paypal_country);

        if (!empty($show_paypal_methods[$config['paypal_solution']]) && $show_paypal_methods[$config['paypal_solution']] != 'Y') {

            $cc_processor = 'ps_paypal_pro.php';
            $config['paypal_solution'] = 'express';

            func_array2insert(
                'config',
                array(
                    'name'  => 'paypal_solution',
                    'value' => $config['paypal_solution'],
                ),
                true
            );

        }

    }

    func_disable_paypal_methods($config['paypal_solution'], false);

    func_header_location("cc_processing.php?mode=update&cc_processor=$cc_processor");
}

/**
 * $cc_processing_module
 */
if ($mode == 'update') {

    require $xcart_dir . '/include/safe_mode.php';

    if (!empty($cc_processor)) {

        require $xcart_dir . '/include/countries.php';

        $cc_processing_module = func_get_pm_params($cc_processor);

        if (empty($cc_processing_module)) {

            $cc_processor_name = func_query_first_cell("SELECT module_name FROM $sql_tbl[ccprocessors] WHERE $sql_tbl[ccprocessors].processor = '$cc_processor'");
            $top_message['content'] =  func_get_langvar_by_name('err_processor_not_included_in_list', array('processor_name' => $cc_processor_name));

            $top_message['type'] = 'E';

            func_header_location('payment_methods.php');

        }

        if ($cc_processor == 'ch_authorizenet.php') {

            $cc_processing_module['param01'] = text_decrypt(trim($cc_processing_module["param01"]));

            if (is_null($cc_processing_module['param01'])) {

                x_log_flag('log_decrypt_errors', 'DECRYPT', "Could not decrypt the field 'param01' for AuthorizeNet: AIM payment module", true);

            }

            $cc_processing_module['param02'] = text_decrypt(trim($cc_processing_module["param02"]));

            if (is_null($cc_processing_module['param02'])) {

                x_log_flag('log_decrypt_errors', 'DECRYPT', "Could not decrypt the field 'param02' for AuthorizeNet: AIM payment module", true);

            }

        } elseif (
            $cc_processor == 'ps_paypal.php'
            || $cc_processor == 'ps_paypal_pro.php'
            || $cc_processor == 'ps_paypal_advanced.php'
            || $cc_processor == 'ps_paypal_payflowlink.php'
            || $cc_processor == 'ps_paypal_pro_hosted.php'
        ) {

            $cc_processing_module['template'] = 'ps_paypal_group.tpl';

            $pp_pro_version = (
                        (
                            $cc_processor == 'ps_paypal.php'
                            && !empty($config['paypal_last_pro_solution'])
                            && $config['paypal_last_pro_solution'] == 'uk'
                        )
                        || (
                            $cc_processor == 'ps_paypal_pro.php'
                            && $config['paypal_solution'] != 'pro'
                            && $config['paypal_solution'] != 'express'
                        )
                    ) ? 'uk' : 'pro';

            $paypal_solutions = array(
                'ipn' => 'ps_paypal.php',
                $pp_pro_version => 'ps_paypal_pro.php',
                'advanced' => 'ps_paypal_advanced.php',
                'payflowlink' => 'ps_paypal_payflowlink.php',
                'pro_hosted' => 'ps_paypal_pro_hosted.php',
            );

            foreach ($paypal_solutions as $solution_name => $solution_processor) {

                if ($solution_processor == $cc_processor) {
                    $conf_data[$solution_name] = $cc_processing_module;
                } else {
                    $conf_data[$solution_name] = func_get_pm_params($solution_processor);
                }

            }

            $smarty->assign('conf_data', $conf_data);

            $default_paypal_email = $config['Company']['orders_department'];

            if (empty($default_paypal_email)) {

                $default_paypal_email = $user_account['email'];

            }

            $smarty->assign('default_paypal_email', $default_paypal_email);

            if (!empty($active_modules['XPayments_Connector'])) {

                func_xpay_func_load();

                $xpc_dp_processors = array(
                    'pro' => false,
                    'uk' => false,
                );

                foreach ($xpc_dp_processors as $k => $v) {

                    if (xpc_is_paypal_dp_exists($k)) {

                        $tmp = xpc_get_paypal_dp_processor($k);

                        $v = array('use' => $tmp['use_xpc']);

                        if ($tmp['warning']) {
                            $v['warning'] = $tmp['warning'];
                        }

                        $v['processors'] = xpc_get_paypal_dp_list($k);

                        $xpc_dp_processors[$k] = $v;

                    }

                }

                $smarty->assign('xpc_dp_processors', $xpc_dp_processors);

            }

            if (!isset($config['paypal_country'])) {
                func_array2insert(
                    'config', 
                    array(
                        'name' => 'paypal_country', 
                        'value' => $config['Company']['location_country']
                    ),
                    true
                );

                $config['paypal_country'] = $config['Company']['location_country'];
            }

            //Hide PayPal payment methods
            if ($config['paypal_country'] != 'none') {

                $show_paypal_methods = func_check_payment_country('ps_paypal.php', $config['paypal_country']);

                $smarty->assign('show_paypal_methods', $show_paypal_methods);
            }


        } elseif ($cc_processor == 'cc_xpc.php') {

            func_header_location('xpc_admin.php#xpc-tabs-payment-methods');

        }

        if (!empty($active_modules['Klarna_Payments'])) {
            func_klarna_set_pclasses($cc_processor, $smarty);
        }

    } 

} // if ($mode == 'update')

if (empty($cc_processing_module))
    func_header_location('payment_methods.php');

$cc_processing_module = func_array_merge($cc_processing_module, test_ccprocessor($cc_processing_module));

$location[] = array(
    func_get_langvar_by_name('lbl_payment_gateways'),
    'cc_processing.php',
);

if ($cc_processing_module) {

    $location[] = array($cc_processing_module['module_name'], '');

    $smarty->assign('pm_currencies', func_pm_get_currencies($cc_processor));

}

// cc_gestpay
if ($cc_processor == 'cc_gestpay.php') {

    $smarty->assign('ric_number', func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[cc_gestpay_data] WHERE type = 'C'"));
    $smarty->assign('ris_number', func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[cc_gestpay_data] WHERE type = 'S'"));

}

if ($cc_processor == 'cc_netbanx.php') {

    $tmp = @unserialize($cc_processing_module['param05']);

    if (is_array($tmp)) {

        $netbanx_ptypes = array();

        foreach ($tmp as $value) {

            $netbanx_ptypes[$value] = true;

        }

        $smarty->assign('netbanx_ptypes', $netbanx_ptypes);

    }

}

if (
    !empty($active_modules['Simplify'])
) {
    switch ($cc_processor) {
        case XC_SIMPLIFY_CC:
            func_header_location("configuration.php?option=Simplify#simplify-tabs-payment-form");
            break;
        case XC_SIMPLIFY_CC_HOSTED:
            func_header_location("configuration.php?option=Simplify#simplify-tabs-hosted-form");
            break;
    }
}

if (
    $cc_processor == 'cc_paypointft.php'
    && $REQUEST_METHOD == 'GET'
) {
    func_pm_load($cc_processor);//for func_cc_paypointft_get_htaccess_code function
        
    $user = $cc_processing_module['param04'];
    $pass = $cc_processing_module['param05'];
    if (empty($user)) {
        $user = substr(md5(uniqid(mt_rand(), true)), 0, 16) . '_user';
        func_array2update('ccprocessors', array('param04' => $user), "processor='cc_paypointft.php'");
        $cc_processing_module['param04'] = $user;
    } 

    if (empty($pass)) {
        $pass = md5(uniqid(mt_rand(), true));
        func_array2update('ccprocessors', array('param05' => $pass), "processor='cc_paypointft.php'");
        $cc_processing_module['param05'] = $pass;
    }  

    $smarty->assign('payment_htaccess_path',$xcart_dir . XC_DS . 'payment' . XC_DS . '.htaccess');
    $smarty->assign('dir_separator', XC_DS);
}

$test_description = func_get_langvar_by_name('txt_test_descr_' . substr($cc_processor, 0, -4), false, false, true);

$currencies = func_query("SELECT * FROM $sql_tbl[currencies]");

$smarty->assign('currencies',                     $currencies);
$smarty->assign('location',                     $location);
$smarty->assign('timezone_offset',                floor(date('Z') / 3600));
$smarty->assign('main',                            'cc_processing');
$smarty->assign('module_data',                    $cc_processing_module);
$smarty->assign('processing_module',            'payments/' . $cc_processing_module['template']);
$smarty->assign('module_test_mode_description', $test_description);

if (is_readable($xcart_dir . '/modules/gold_display.php')) {
    include $xcart_dir . '/modules/gold_display.php';
}

func_display('admin/home.tpl', $smarty);

?>
