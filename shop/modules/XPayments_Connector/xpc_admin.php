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
 * X-Payments Connector admin back-end
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    18315097ccc28a2681a4a1cfeae82bc2c458f7b3, v46 (xcart_4_7_0), 2015-02-19 15:36:46, xpc_admin.php, random
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_SESSION_START') ) { header('Location: ../../'); die('Access denied'); }

func_xpay_func_load();

$is_module_configured = xpc_is_module_configured();

x_session_register('xpc_postponed_deploy_configuration', false);

if ($xpc_postponed_deploy_configuration) {
    x_session_unregister('xpc_postponed_deploy_configuration');
    $mode = 'deploy_configuration';
    $configure_after_deploy = true;
}

if ('POST' == $REQUEST_METHOD || $xpc_postponed_deploy_configuration) {

    require $xcart_dir . '/include/safe_mode.php';

    if ('POST' == $REQUEST_METHOD) {
        // Encrypt sensitive data
        foreach ($xpc_crypted_map_fields as $field) {
            if (isset($_POST[$field])) {
                $_POST[$field] = text_crypt(stripslashes($_POST[$field]));
            }    
        }
    }

    if ('deploy_configuration' == $mode) {

        func_array2insert('config', array('name' => 'xpc_skip_welcome', 'value' => 'Y'), true);

        $xpc_redirect_url = 'xpc_admin.php#xpc-tabs-payment-methods';

        if (isset($_POST['deploy_configuration'])) {
            $xpc_config = func_xpc_get_configuration($_POST['deploy_configuration']);

            if (true === func_xpc_check_deploy_configuration($xpc_config)) {

                func_xpc_store_configuration($xpc_config);

                $top_message = array(
                    'type'      => 'I',
                    'content'   => func_get_langvar_by_name('txt_xpc_msg_configuration_deploy_success'). '<br />',
                );  

                 $configure_after_deploy = true;

            } else {

                $top_message = array(
                    'type'      => 'E',
                    'content'   => func_get_langvar_by_name('txt_xpc_msg_configuration_deploy_fail'),
                );

                $xpc_redirect_url = 'xpc_admin.php?mode=deploy_configuration';

            }

        } elseif (empty($configure_after_deploy)) {
            unset($_POST['mode']);
            $xpc_postponed_deploy_configuration = true;
            return;
        }

        if (!empty($configure_after_deploy)) {

            // Autodetect API, test moduleand request payment methods
            $is_updated = func_xpc_process_configuration_update(true);

            if (
                !$is_updated
            ) {
                 $xpc_redirect_url = 'xpc_admin.php?mode=deploy_configuration';
            }

        }

        func_header_location($xpc_redirect_url);

    } elseif ('update_payment_methods' == $mode) {

        $has_paypal = false;
        $is_paypal = array('active' => false, 'use_recharges' => false);

        $pp_processor = xpc_get_paypal_dp_processor($config['paypal_solution']);

        if (false != $pp_processor['use_xpc'] && !empty($pp_processor['processor'])) {
            foreach ($is_paypal as $var => $value) {
                if (!empty($$var)) {
                    $paypal_key = array_search($pp_processor['processor']['paymentid'], $$var);

                    if ($paypal_key !== false) {
                        $has_paypal = true;
                        $is_paypal[$var] = true;
                        unset(${$var}[$paypal_key]);
                    }
                }
            }
        }

        db_query("UPDATE $sql_tbl[payment_methods] SET use_recharges = 'N', active = 'N' WHERE processor_file IN ('cc_xpc.php'" . (!empty($has_paypal) ? ", 'ps_paypal_pro.php'" : '') . ")");

        // Update params iterating $is_paypal keys as a reference for param names
        foreach (array_keys($is_paypal) as $param_name) {
            if (!empty($$param_name) && is_array($$param_name) || $is_paypal[$param_name]) {
                db_query("UPDATE $sql_tbl[payment_methods] SET $param_name = 'Y' WHERE (paymentid IN ('" . implode("', '", $$param_name) ."') AND processor_file = 'cc_xpc.php')" . ($is_paypal[$param_name] ? " OR processor_file = 'ps_paypal_pro.php'" : ''));
            }
        }

        if (!empty($use_recharges) && is_array($use_recharges) || $is_paypal['use_recharges']) {

            $xpc_recharge_payment_exists = func_query_first_cell("SELECT count(*) FROM $sql_tbl[payment_methods] WHERE payment_script = 'payment_xpc_recharge.php'");

            // Add X-Payments recharge payment method
            if (!$xpc_recharge_payment_exists) {

                $xpc_recharge_payment = array(
                    'payment_method'    => 'Use saved credit card',
                    'payment_details'   => '',
                    'payment_template'  => 'modules/XPayments_Connector/payment_recharge.tpl',
                    'payment_script'    => 'payment_xpc_recharge.php',
                    'protocol'          => 'http',
                    'orderby'           => '999',
                    'active'            => 'Y',
                    'is_cod'            => 'N',
                    'af_check'          => 'N',
                    'processor_file'    => '',
                    'surcharge'         => '0.00',
                    'surcharge_type'    => '$',
                );

                func_array2insert('payment_methods', $xpc_recharge_payment);
            }

        }  else {
            // Remove X-Payments recharge payment method
            db_query("DELETE FROM $sql_tbl[payment_methods] WHERE payment_script = 'payment_xpc_recharge.php'");
        }

        $top_message = array(
            'type'      => 'I',
            'content'   => func_get_langvar_by_name('msg_adm_payment_methods_upd'),
        );

        func_header_location('xpc_admin.php');

    } else {

        if (!isset($xpc_use_iframe) || $xpc_use_iframe != 'on') {
            $_POST['xpc_use_iframe'] = 'N';
        }

        return;
    }

}

$mode = !empty($_GET['mode']) ? $_GET['mode'] : '';

// Check if $mode is enabled for XPayments_Connector option
if (!in_array($mode, array('deploy_configuration', 'welcome', 'import_payment_methods', 'check_callback', ''))) {
    func_page_not_found();
}

if (!$is_module_configured && !empty($mode) && !in_array($mode, array('deploy_configuration', 'welcome'))) {
    func_header_location('xpc_admin.php');
}

if (empty($mode) && !$is_module_configured) {
    func_header_location('xpc_admin.php?mode=welcome');
}

if ($mode == 'import_payment_methods') {
    require $xcart_dir . '/include/safe_mode.php';
    func_xpc_process_configuration_update();
    func_header_location('xpc_admin.php#xpc-tabs-payment-methods');
} elseif ($mode == 'check_callback') {
    x_session_unregister('xpc_check_callback');
    func_header_location('xpc_admin.php');
}

$smarty->assign('mode', $mode);

$smarty->assign('is_module_configured', $is_module_configured);

$is_check_requirements = xpc_check_requirements();

$check_requirements_errs = array();

if ($is_check_requirements & XPC_REQ_CURL) {
    $check_requirements_errs[] = func_get_langvar_by_name('txt_xpc_reqerr_curl');
}

if ($is_check_requirements & XPC_REQ_OPENSSL) {
    $check_requirements_errs[] = func_get_langvar_by_name('txt_xpc_reqerr_openssl');
}

if ($is_check_requirements & XPC_REQ_DOM) {
    $check_requirements_errs[] = func_get_langvar_by_name('txt_xpc_reqerr_dom');
}

x_session_register('xpc_check_callback', null);

if (is_null($xpc_check_callback)) {
    $xpc_check_callback = func_xpc_is_callback_reachable();
}
if (!$xpc_check_callback) {
    $check_requirements_errs[] = func_get_langvar_by_name('txt_xpc_callback_not_accessible', array('url' => $https_location . '/payment/cc_xpc.php'));
}

if (count($check_requirements_errs) > 0) {
    $smarty->assign('system_requirements_errors', $check_requirements_errs);
}

$module_configured_status = xpc_get_module_system_errors();

$check_sys_errs = array();

if ($module_configured_status & XPC_SYSERR_CARTID) {
    $check_sys_errs['xpc_shopping_cart_id'] = func_get_langvar_by_name('txt_xpc_syserr_cartid');
}

if ($module_configured_status & XPC_SYSERR_URL) {
    $check_sys_errs['xpc_xpayments_url'] = func_get_langvar_by_name('txt_xpc_syserr_url');
}

if ($module_configured_status & XPC_SYSERR_PUBKEY) {
    $check_sys_errs['xpc_public_key'] = func_get_langvar_by_name('txt_xpc_syserr_pubkey');
}

if ($module_configured_status & XPC_SYSERR_PRIVKEY) {
    $check_sys_errs['xpc_private_key'] = func_get_langvar_by_name('txt_xpc_syserr_privkey');
}

if ($module_configured_status & XPC_SYSERR_PRIVKEYPASS) {
    $check_sys_errs['xpc_private_key_password'] = func_get_langvar_by_name('txt_xpc_syserr_privkeypass');
}

if (count($check_sys_errs) > 0 && !empty($config['xpc_skip_welcome'])) {
    $smarty->assign('check_sys_errs', $check_sys_errs);
}

$xpc_recommends = xpc_check_pci_dss_requirements();

list(
    $warning_fields,
    $error_fields
) = xpc_check_fields();

if (false !== $error_fields) {

    $xpc_recommends['E']['error_fields'] = func_get_langvar_by_name(
        'txt_xpc_profiles_fields_error', 
        array(
            'fields' => $error_fields,
        )
    );

} elseif (false !== $warning_fields) {

    $xpc_recommends['W']['warning_fields'] = func_get_langvar_by_name(
        'txt_xpc_profiles_fields_warning', 
        array(
            'fields' => $warning_fields,
        )
    );

}

if (!empty($xpc_recommends)) {
    $smarty->assign('xpc_recommends', $xpc_recommends);
}

$currencies = func_query_hash("SELECT code, name FROM $sql_tbl[currencies] ORDER BY name", 'code', false, true);

$available_save_cc_processors = array();
$no_active_payment_methods = true;

$cc_processors = func_xpc_get_cc_processors();
$pp_processor = xpc_get_paypal_dp_processor($config['paypal_solution']);

foreach ($cc_processors as $key => $processor) {
    if (
        !empty($pp_processor['processor'])
        && $processor['paymentid'] == $pp_processor['processor']['paymentid']
        && false != $pp_processor['use_xpc']
        && ('local' == $pp_processor['use'] || $pp_processor['pm_not_found'])
    ) {
        // Something is wrong with the PayPal configuration
        $cc_processors[$key]['paypal_error'] = true;
        $cc_processors[$key]['can_recharge'] = false;
        $cc_processors[$key]['currency'] = '';
    } else {
        $cc_processors[$key]['can_recharge'] = xpc_can_recharge($processor['paymentid']);
        $cc_processors[$key]['currency'] = xpc_get_supported_currency($processor['paymentid']);
    }

    if ($cc_processors[$key]['can_recharge'] && $processor['has_preauth'] == 'Y' && $processor['active'] == 'Y' && $processor['use_recharges'] == 'Y') {
        $available_save_cc_processors[$processor['paymentid']] = $processor['module_name'];
    }
    $cc_processors[$key]['currency_name'] = isset($currencies[$cc_processors[$key]['currency']]) ? $currencies[$cc_processors[$key]['currency']] : '';
    if ($processor['active'] == 'Y') {
        $no_active_payment_methods = false;
    }
    
}

$smarty->assign('cc_processors', $cc_processors);

$smarty->assign('no_active_payment_methods', $no_active_payment_methods);

$xpc_save_cc_paymentid = func_xpc_get_save_cc_paymentid();
$smarty->assign('no_save_cc_processors_avail', ($xpc_save_cc_paymentid == 0));

$advanced_settings = array(
    'xpc_shopping_cart_id',
    'xpc_xpayments_url',
    'xpc_public_key',
    'xpc_private_key',
    'xpc_private_key_password',
);

$hidden_settings = array(
    'xpc_api_version',
);

$xpc_configuration = array('common' => array(), 'advanced' => array(), 'mapping_rules' => array(), 'save_cc' => array(), 'hidden' => array());

$configuration = func_query("SELECT * FROM $sql_tbl[config] WHERE category = 'XPayments_Connector' ORDER BY orderby");

foreach ($configuration as $k => $v) {

    // Post-processing here is limited to X-Payments needs, e.g. doesn't support multiselectors
    if ($v['type'] == 'separator') {
        continue;
    }

    if ($v['name'] == 'xpc_currency') {
        // Currency list
        if (!empty($currencies)) {
            $v['variants'] = $currencies;
        } else {
            $v['type'] = 'text';
        }
    } elseif ($v['name'] == 'xpc_save_cc_paymentid') {
        // List of active payment methods with recharge enabled
        $v['type'] = 'selector';
        $v['variants'] = $available_save_cc_processors;
        $v['value'] = $xpc_save_cc_paymentid;
    } elseif ($v['name'] == 'xpc_save_cc_amount') {
        $v['value'] = price_format($v['value']);
    } elseif (in_array($v['name'], $xpc_crypted_map_fields)) {
        // Decrypt sensitive data
        $v['value'] = text_decrypt($v['value']);

    }

    if ($v['type'] == 'selector') {
        if (!is_array($v['variants'])) {
            $vars = func_parse_str(trim($v['variants']), "\n", ':');
            $vars = func_array_map('trim', $vars);
        } else {
            $vars = $v['variants'];
        }
        $v['variants'] = array();
        foreach ($vars as $vk => $vv) {
            if (!empty($vv) && strpos($vv, '_') !== false && strpos($vv, ' ') === false) {
                $name = func_get_langvar_by_name(addslashes($vv), NULL, false, true);
                if (!empty($name)) {
                    $vv = $name;
                }
            }
            $v['variants'][$vk] = array(
                'name' => $vv,
                'selected' => ($v['value'] == $vk)
            );
        }

    }

    $predefined_lng_variables[] = 'opt_' . $v['name'];
    $predefined_lng_variables[] = 'opt_descr_' . $v['name'];

    if (strpos($v['name'], 'xpc_status') === 0) {
        $xpc_configuration['mapping_rules'][] = $v;
    } elseif (strpos($v['name'], 'xpc_save_cc') === 0) {
        $xpc_configuration['save_cc'][] = $v;
    } elseif (in_array($v['name'], $advanced_settings)) {
        $xpc_configuration['advanced'][] = $v;
    } elseif (in_array($v['name'], $hidden_settings)) {
        $xpc_configuration['hidden'][] = $v;
    } else {
        $xpc_configuration['common'][] = $v;
    }

}
$smarty->assign('xpc_configuration', $xpc_configuration);

$xp_backend_url = $config['XPayments_Connector']['xpc_xpayments_url'] . '/admin.php?target=shopping_carts';
$smarty->assign('xp_backend_url', $xp_backend_url);

$xpc_config_tabs = array();

$xpc_config_tabs[] = array(
        'title' => func_get_langvar_by_name('lbl_xpc_tabs_payment_methods'),
        'tpl' => 'modules/XPayments_Connector/admin_tab_payment_methods.tpl',
        'anchor' => 'payment-methods',
    );

$xpc_config_tabs[] = array(
        'title' => func_get_langvar_by_name('lbl_xpc_tabs_mapping_rules'),
        'tpl' => 'modules/XPayments_Connector/admin_tab_mapping_rules.tpl',
        'anchor' => 'mapping-rules',
    );

if (defined('XPC_API_1_3_COMPATIBLE')) {
    $xpc_config_tabs[] = array(
            'title' => func_get_langvar_by_name('lbl_xpc_tabs_save_cc_setup'),
            'tpl' => 'modules/XPayments_Connector/admin_tab_save_cc_setup.tpl',
            'anchor' => 'save-cards-setup',
        );
}

$xpc_config_tabs[] = array(
        'title' => func_get_langvar_by_name('lbl_xpc_tabs_connection'),
        'tpl' => 'modules/XPayments_Connector/admin_tab_connection.tpl',
        'anchor' => 'connection',
    );

$smarty->assign('xpc_config_tabs', $xpc_config_tabs);


?>
