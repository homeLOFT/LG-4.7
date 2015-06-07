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
 * For explanation of Payment Methods please refer to
 * X-Cart developer's documentation
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Admin interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    0ceaf7c079b2b533578e24b21b0dcb1df8237d96, v121 (xcart_4_7_1), 2015-03-19 09:45:37, payment_methods.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

define('IS_MULTILANGUAGE', 1);

require __DIR__.'/auth.php';
require $xcart_dir . '/include/security.php';

x_load(
    'backoffice',
    'tests',
    'paypal',
    'payment'
);

if (!empty($active_modules['XPayments_Connector'])) {
    func_xpay_func_load();
}

$location[] = array(func_get_langvar_by_name('lbl_payment_methods'), '');

if ($REQUEST_METHOD == 'POST') {

    require $xcart_dir . '/include/safe_mode.php';

    if ($mode == 'add_paypal') {

        $paymentid = func_add_processor('ps_paypal.php', 0);

        func_array2insert(
            'config',
            array(
                'name'  => 'paypal_solution',
                'value' => 'express',
            ),
            TRUE
        );

        func_header_location("cc_processing.php?mode=update&cc_processor=ps_paypal.php");

    } elseif ($mode == 'change_force_offline_paymentid'){
        func_array2update(
            'config',
            array(
                'value' => $force_offline_paymentid,
            ),
            "name = 'force_offline_paymentid'"
        );
        $top_message['content'] = func_get_langvar_by_name('msg_adm_payment_methods_upd');
        $top_message['anchor'] = 'section_force_offline_paymentid';
    } else {

        if (is_array($posted_data)) {

            $paypal_directid = func_query_first_cell("SELECT $sql_tbl[payment_methods].paymentid FROM $sql_tbl[payment_methods], $sql_tbl[ccprocessors] WHERE $sql_tbl[payment_methods].processor_file='ps_paypal_pro.php' AND $sql_tbl[payment_methods].processor_file=$sql_tbl[ccprocessors].processor AND $sql_tbl[payment_methods].paymentid<>$sql_tbl[ccprocessors].paymentid");

            foreach ($posted_data as $k => $v) {
                settype($v['surcharge'], 'float');
                settype($v['surcharge_type'], 'string');

                $v['active']     = (!empty($v['active']) ? 'Y' : 'N');
                $v['is_cod']     = (!empty($v['is_cod']) ? 'Y' : 'N');
                $v['af_check']     = (!empty($v['af_check']) ? 'Y' : 'N');
                $v['surcharge'] = func_convert_number($v['surcharge']);

                if ($v['surcharge_type'] != "%") {
                    $v['surcharge_type'] = "$";
                }

                func_languages_alt_insert('payment_method_' . $k, $v['payment_method'], $shop_language);
                func_languages_alt_insert('payment_details_' . $k, $v['payment_details'], $shop_language);

                if ($shop_language != $config['default_admin_language']) {
                    unset($v['payment_method'], $v['payment_details']);
                }

                func_membership_update('pmethod', $k, $v['membershipids'], 'paymentid');

                unset($v['membershipids']);

                if (!empty($v['payment_method'])) {
                    $v['payment_method'] = rtrim(func_substr($v['payment_method'], 0, 255), '\\');
                }

                func_array2update(
                    'payment_methods',
                    $v,
                    "paymentid = '$k'"
                );

                if (
                    $paypal_directid
                    && $paypal_directid == $k
                ) {
                    func_array2update(
                        'payment_methods',
                        array(
                            'orderby' => $v['orderby'],
                        ),
                        "processor_file = 'ps_paypal_pro.php'"
                    );
                }

            }

            func_disable_paypal_methods($config['paypal_solution']);

            func_check_force_offline_paymentid_for_cod();

            $top_message['content'] = func_get_langvar_by_name('msg_adm_payment_methods_upd');

        } else {

            $top_message['content'] = func_get_langvar_by_name('msg_adm_err_payment_methods_upd');

            $top_message['type'] = 'E';

        }
    }

    func_header_location('payment_methods.php');
}

/**
 * Obtain payment methods
 */
$payment_methods = func_query("SELECT pm.*, cc.module_name, cc.processor, cc.type, cc.param01 FROM $sql_tbl[payment_methods] AS pm LEFT JOIN $sql_tbl[ccprocessors] AS cc ON (pm.paymentid=cc.paymentid OR pm.paymentid<>cc.paymentid AND pm.processor_file=cc.processor AND cc.processor != 'cc_xpc.php') ORDER BY pm.active DESC, pm.orderby, pm.paymentid");

$payment_methods = test_payment_methods($payment_methods);

$list_is_empty = func_is_pmethods_list_empty($payment_methods);

func_populate_payment_tabs();

x_session_register('recent_payment_methods');

$smarty->assign('recent_payment_methods',     $recent_payment_methods);
$smarty->assign('list_is_empty',             $list_is_empty);

/**
 * Hide not usable PayPal methods
 */
$is_paypal_exists = false;
$is_paypal_enabled = false;

if (is_array($payment_methods)) {

    $_payment_methods = array();

    foreach ($payment_methods as $pm) {

        $skip = false;

        if (func_is_paypal_processor($pm['processor_file'])) {

            $is_paypal_enabled = true;

            $pm['disable_extra_charge'] = true;

            if ($pm['processor_file'] == 'ps_paypal_bml.php') {
                if (!empty($active_modules['Bill_Me_Later'])) {
                    $pm['disable_checkbox'] = TRUE;
                } else {
                    $skip = TRUE;
                }
            }

            // $config['paypal_solution'] = [ ipn | pro | express | uk ]
            switch ($config['paypal_solution']) {
                case 'ipn':

                    if ($pm['processor_file'] == 'ps_paypal.php') {
                        if (!empty($active_modules['Bill_Me_Later'])) {
                            $pm['control_checkbox'] = TRUE;
                        }
                    } elseif ($pm['processor_file'] != 'ps_paypal_bml.php') {
                        $skip = TRUE;
                    }

                    break;

                case 'pro':
                case 'uk':

                    if (
                        $pm['processor_file'] != 'ps_paypal_pro.php'
                        && $pm['processor_file'] != 'ps_paypal_bml.php'
                    ) {
                        $skip = TRUE;
                    } else {
                        $is_paypal_exists = TRUE;
                    }

                    if ($pm['processor_file'] != 'ps_paypal_bml.php') {
                        if (strpos($pm['payment_template'], 'payment_offline.tpl') !== FALSE) {

                            // This is embedded Express checkout method
                            $pm['module_name'] = 'PayPal Express Checkout';
                            $pm['disable_checkbox'] = TRUE;

                        } else {

                            $pm['note'] = func_get_langvar_by_name('txt_paypal_pro_pg_tooltip');
                            $pm['control_checkbox'] = TRUE;

                        }
                    }

                    break;

                case 'pro_hosted':

                    if (
                        $pm['processor_file'] != 'ps_paypal_pro_hosted.php'
                        && $pm['processor_file'] != 'ps_paypal_bml.php'
                        && (
                            $pm['processor_file'] != 'ps_paypal_pro.php'
                            || strpos($pm['payment_template'], 'payment_offline.tpl') === FALSE
                        )
                    ) {

                        $skip = TRUE;

                    } else {

                        $is_paypal_exists = TRUE;

                    }

                    if ($pm['processor_file'] != 'ps_paypal_bml.php') {
                        if (strpos($pm['payment_template'], 'payment_offline.tpl') !== FALSE) {

                            // This is embedded Express checkout method
                            $pm['module_name'] = 'PayPal Express Checkout';
                            $pm['disable_checkbox'] = TRUE;

                        } else {

                            $pm['control_checkbox'] = TRUE;

                        }
                    }

                    break;

                case 'advanced':
                case 'payflowlink':

                    $_solution_processor = ($config['paypal_solution'] == 'advanced') ? 'ps_paypal_advanced.php' : 'ps_paypal_payflowlink.php';

                    if (
                        $pm['processor_file'] != $_solution_processor
                        && $pm['processor_file'] != 'ps_paypal_bml.php'
                        && (
                            $pm['processor_file'] != 'ps_paypal_pro.php'
                            || strpos($pm['payment_template'], 'payment_offline.tpl') === FALSE
                        )
                    ) {

                        $skip = TRUE;

                    } else {

                        $is_paypal_exists = TRUE;

                    }

                    if ($pm['processor_file'] != 'ps_paypal_bml.php') {
                        if (strpos($pm['payment_template'], 'payment_offline.tpl') !== FALSE) {

                            // This is embedded Express checkout method
                            $pm['module_name'] = 'PayPal Express Checkout';
                            $pm['disable_checkbox'] = TRUE;

                        } else {

                            $pm['control_checkbox'] = TRUE;

                        }
                    }

                    unset($_solution_processor);

                    break;

                case 'express':

                    if (
                        $pm['processor_file'] == 'ps_paypal.php'
                        || (
                            strpos($pm['payment_template'], 'payment_offline.tpl') === FALSE
                            && $pm['processor_file'] != 'ps_paypal_bml.php'
                        )
                    ) {

                        $skip = TRUE;

                    } else {

                        $is_paypal_exists = TRUE;

                    }

                    if ($pm['processor_file'] == 'ps_paypal_pro.php') {
                        $pm['module_name'] = 'PayPal Express Checkout';
                        if (!empty($active_modules['Bill_Me_Later'])) {
                            $pm['control_checkbox'] = TRUE;
                        }
                    }

                    break;
            }
            
            if ($config['paypal_country'] != 'none') {
                $show_paypal_methods = func_check_payment_country('ps_paypal.php', $config['paypal_country']);
                if ($show_paypal_methods[$config['paypal_solution']] == 'N') {
                    $pm['disable_checkbox'] = TRUE;
                }
            }
            
        }

        if ($skip) {

            continue;

        }

        $_payment_methods[] = $pm;

    }

    $payment_methods = array_values($_payment_methods);

    // PayPal Pro methods sorting
    if (
        $is_paypal_enabled
        && (
            $config['paypal_solution'] == 'pro'
            || $config['paypal_solution'] == 'uk'
        )
    ) {

        $i1 = false;
        $i2 = false;

        foreach ($payment_methods as $k => $pm) {

            if (
                $pm['processor_file'] == "ps_paypal.php"
                || $pm['processor_file'] == "ps_paypal_pro.php"
            ) {

                if (preg_match('/payment_cc\.tpl$/Ss', $pm["payment_template"])) {

                    $i1= $k;

                } else {

                    $i2 = $k;

                }

            }

        }

        $tmp = $payment_methods[$i2];

        $payment_methods[$i2] = $payment_methods[$i1];

        $payment_methods[$i1] = $tmp;

    }

}

if (!empty($payment_methods)) {

    foreach ($payment_methods as $k => $v) {

        $tmp = func_get_languages_alt('payment_method_' . $v['paymentid']);

        if (!empty($tmp)) {

            $payment_methods[$k]['payment_method'] = $tmp;

        }

        $tmp = func_get_languages_alt('payment_details_' . $v['paymentid']);

        if (!empty($tmp)) {

            $payment_methods[$k]['payment_details'] = $tmp;

        }

        $tmp = func_query_column("SELECT membershipid FROM $sql_tbl[pmethod_memberships] WHERE paymentid = '$v[paymentid]'");

        if (!empty($tmp)) {

            $payment_methods[$k]['membershipids'] = array();

            foreach ($tmp as $mid) {

                $payment_methods[$k]['membershipids'][$mid] = 'Y';

            }

        }

    }

}

$possible_xp_ccprocessors = '';

if (
    empty($active_modules['XPayments_Connector'])
    || !xpc_is_payment_methods_exists()
) {
    $possible_xp_ccprocessors = func_get_xp_processors('in_sql');
}

$paypal_here_condition = '';
if (empty($active_modules['PayPal_Here'])) {
    $paypal_here_condition = ' AND processor != \'pp_paypal_here.php\' ';
}

// Modules are ordered alphabetically except for Payment systems group
$cc_module_files = func_query("(SELECT * FROM $sql_tbl[ccprocessors] WHERE paymentid='0' AND processor NOT LIKE 'ps\_paypal\_%' $paypal_here_condition) $possible_xp_ccprocessors ORDER BY (type != 'P'), type, (processor NOT LIKE 'ps_paypal%'), module_name");

if (!empty($active_modules['XPayments_Connector'])) {

    $cc_module_files = xpc_filter_hidden_processors($cc_module_files);

}

if (!$is_paypal_exists) {
    $banner_tools_data[] = array (
        'template' => 'admin/main/paypal_pec.tpl',
    );
}

$dialog_tools_data['right'][] = array(
 'link' => 'configuration.php?option=Company',
 'title' => func_get_langvar_by_name('option_title_Company'),
);

$dialog_tools_data['right'][] = array(
 'link' => 'configuration.php?option=Security',
 'title' => func_get_langvar_by_name('option_title_Security'),
);

$dialog_tools_data['right'][] = array(
 'link' => 'countries.php',
 'title' => func_get_langvar_by_name('lbl_countries'),
);

if (!empty($active_modules['XPayments_Connector'])) {
    $dialog_tools_data['right'][] = array(
        'link' => 'configuration.php?option=XPayments_Connector',
        'title' => func_get_langvar_by_name('module_name_XPayments_Connector'),
    );
}

if ($config['Company']['location_country'] == 'GB') {
    $dialog_tools_data['right'][] = array(
        'link' => "http://www.x-cart.com/sagepay.html",
        'title' => func_get_langvar_by_name('lbl_sage_pay_special_offer'),
        'target' => '_blank'
    );
}

$check_active_payments = func_check_active_payments();

if ($check_active_payments !== TRUE) {

    $smarty->assign(
        'top_message',
        array(
            'type'         => 'W',
            'content'     => $check_active_payments,
        )
    );
}

$banners = func_get_internal_banners('payment');
$payment_banners = array();

if (
    is_array($banners) 
    && !empty($banners)
) {
    foreach ($banners as $banner) {
        $payment_banners[$banner['param01']] = $banner['param02'];
    }
}

$payment_countries = func_get_payment_countries();
if (in_array($config['Company']['location_country'], array_keys($payment_countries['countries']))) {
    $selected_payment_country = array(
        'code' => $config['Company']['location_country'],
        'name' => $payment_countries['countries'][$config['Company']['location_country']],
    );
}

$smarty->assign('is_paypal_exists',         $is_paypal_exists);
$smarty->assign('cc_modules',               $cc_module_files);
$smarty->assign('payment_countries',        $payment_countries);
$smarty->assign('payment_banners',          $payment_banners);
$smarty->assign('memberships',              func_get_memberships());
$smarty->assign('payment_methods',          $payment_methods);
$smarty->assign('main',                     'payment_methods');

if (isset($dialog_tools_data)) {
    $smarty->assign('dialog_tools_data', $dialog_tools_data);
}

if (isset($banner_tools_data)) {
    $smarty->assign('banner_tools_data', $banner_tools_data);
}

if (isset($selected_payment_country)) {
    $smarty->assign('selected_payment_country', $selected_payment_country);
}


// Assign the current location line
$smarty->assign('location', $location);

if (is_readable($xcart_dir . '/modules/gold_display.php')) {
    include $xcart_dir . '/modules/gold_display.php';
}

func_display('admin/home.tpl', $smarty);

?>
