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
 * Shipping methods management
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Admin interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v80 (xcart_4_7_0), 2015-02-17 13:29:01, shipping.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

require __DIR__.'/auth.php';
require $xcart_dir.'/include/security.php';

$max_input_vars = ini_get('max_input_vars');
if (empty($max_input_vars)) {
    $max_input_vars = 1000;
}

$location[] = array(func_get_langvar_by_name('lbl_shipping_methods'), 'shipping.php');

$intershipper_cond = $config['Shipping']['use_intershipper'] == 'Y'
    ? " AND (intershipper_code!='' OR code='') "
    : "";

$carriers = func_query("SELECT code, shipping, COUNT(*) as total_methods FROM $sql_tbl[shipping] WHERE code!='' $intershipper_cond  GROUP BY code ORDER BY code");

$carrier_valid = false;

if (!empty($carriers)) {
    $carrier_names = array (
        'CPC' => "Canada Post",
        'USPS' => 'U.S.P.S.',
        'APOST' => "Australia Post",
    );

    foreach ($carriers as $k=>$v) {
        if ($v['code'] == @$carrier)
            $carrier_valid = true;

        $_carrier_total_enabled = func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[shipping] WHERE code='$v[code]' AND active='Y' $intershipper_cond ");

        if (!empty($carrier_names[$v['code']]))
            $_carrier_name = $carrier_names[$v['code']];
        else
            $_carrier_name = preg_replace("/^([^ ]+).*$/", "\\1", $v['shipping']);

        $carriers[$k]['shipping'] = $_carrier_name;
        $carriers[$k]['total_enabled'] = $_carrier_total_enabled;
    }
}

if (!$carrier_valid)
    $carrier = '';

if ($REQUEST_METHOD == 'POST') {

    if (!empty($mode) && $mode == 'add_realtime_methods') {
        db_query("UPDATE $sql_tbl[shipping] SET active = 'N' WHERE code != ''");
    }

    if (!empty($data)) {
        foreach ($data as $id => $arr) {
            if ($mode == 'add_realtime_methods' || isset($arr['shipping'])) {
                $arr['active'] = isset($arr['active']) ? 'Y' : 'N';
            } else {
                $arr['active'] = 'Y';
            }
            $arr['is_cod'] = isset($arr['is_cod']) ? 'Y' : 'N';
            if (empty($arr['weight_min'])) {
                $arr['weight_min'] = 0;
            }
            if (empty($arr['weight_limit'])) {
                $arr['weight_limit'] = 0;
            }
            $arr['weight_min'] = func_convert_number($arr['weight_min']);
            $arr['weight_limit'] = func_convert_number($arr['weight_limit']);
            func_array2update('shipping', $arr, "shippingid = '$id'");
        }
    }

    if (!empty($add['shipping'])) {
        $add['active'] = isset($add['active']) ? 'Y' : 'N';
        $add['is_cod'] = isset($add['is_cod']) ? 'Y' : 'N';
        if (empty($add['weight_min'])) {
            $add['weight_min'] = 0;
        }
        if (empty($add['weight_limit'])) {
            $add['weight_limit'] = 0;
        }
        $add['weight_min'] = func_convert_number($add['weight_min']);
        $add['weight_limit'] = func_convert_number($add['weight_limit']);
        func_array2insert('shipping', $add);
    }

    $top_message['content'] = func_get_langvar_by_name('msg_adm_shipping_methods_upd');

    func_header_location('shipping.php'.(!empty($carrier) ? "?carrier=$carrier" : ''));
}

if ($mode == 'delete') {
/**
 * Delete shipping option & associated info
 */
    db_query("DELETE FROM $sql_tbl[shipping] WHERE shippingid='$shippingid'");
    db_query("DELETE FROM $sql_tbl[shipping_rates] WHERE shippingid='$shippingid'");
    db_query("DELETE FROM $sql_tbl[delivery] WHERE shippingid='$shippingid'");

    $top_message['content'] = func_get_langvar_by_name('msg_adm_shipping_method_del');

    func_header_location('shipping.php');
}

$condition = '';

if (!empty($active_modules['UPS_OnLine_Tools']) && $config['Shipping']['use_intershipper'] != 'Y') {
    include $xcart_dir.'/modules/UPS_OnLine_Tools/ups_shipping_methods.php';
}

if ($mode != 'add_realtime_methods') {
    $condition .= " AND (code = '' OR active = 'Y' OR is_new = 'Y')";
    $smarty->assign('main', 'shipping');
} else {
    $smarty->assign('main', 'add_realtime_methods');
    $location[] = array(func_get_langvar_by_name('lbl_add_remove_shipping_methods'), '');
}

$shipping = func_query("SELECT * FROM $sql_tbl[shipping] WHERE 1 $condition $intershipper_cond ORDER BY orderby, shipping");
$new_shipping = '';
$active_shipping_vars = 0;

if (!empty($shipping)) {
    foreach ($shipping as $v) {
        if ($v['is_new'] == 'Y') {
            $new_shipping = 'Y';
        }

        if ($v['active'] == 'Y' || $v['code'] == '' || $v['is_new'] == 'Y') {
            $active_shipping_vars += 4;
            if ($v['code'] == '' || $v['is_new'] == 'Y') {
                $active_shipping_vars += 3;
            }
        }
    }
}

if ($active_shipping_vars >= $max_input_vars) {
    $top_message['type'] = 'W';
    $top_message['content'] = func_get_langvar_by_name('msg_adm_shipping_methods_max_input_vars_exceeded');
    $smarty->assign('top_message', $top_message);
} else {
    $smarty->assign('allow_check_all_for_usps', true);
}

$smarty->assign('shipping', $shipping);
$smarty->assign('new_shipping', $new_shipping);
$smarty->assign('carriers', $carriers);
$smarty->assign('carrier', $carrier);

// Assign the current location line
$smarty->assign('location', $location);

include $xcart_dir . DIR_ADMIN . '/shipping_tools.php';

// Assign the section navigation data
$smarty->assign('dialog_tools_data', $dialog_tools_data);

if (is_readable($xcart_dir.'/modules/gold_display.php')) {
    include $xcart_dir.'/modules/gold_display.php';
}
func_display('admin/home.tpl',$smarty);
?>
