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
 * SECURETRADING
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Payment interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v48 (xcart_4_7_0), 2015-02-17 13:29:01, cc_securetrading.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (isset($_GET['return']) && $_GET['return'] == '1' && isset($_GET['id']) && !empty($_GET['id'])) {

    // Return

    require __DIR__.'/auth.php';

    if (defined('SECURETRADING_DEBUG')) {
        func_pp_debug_log('securetrading', 'R', $_GET);
    }

    if (!func_is_active_payment('cc_securetrading.php'))
        exit;

    $skey = $_GET['id'];
    require($xcart_dir.'/payment/payment_ccview.php');

} elseif ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST["orderref"])) {

    // Callback

    require __DIR__.'/auth.php';

    if (defined('SECURETRADING_DEBUG')) {
        func_pp_debug_log('securetrading', 'C', $_POST);
    }

    if (!func_is_active_payment('cc_securetrading.php'))
        exit;

    $skey = $_POST["orderref"];
    $bill_output["sessid"] = func_query_first_cell("SELECT sessid FROM $sql_tbl[cc_pp3_data] WHERE ref='$skey'");
    $bill_output['code'] = ($stresult == 1) ? 1 : 2;
    
    if ($stauthcode == 'DECLINED' || $stauthcode == 'REQUEST INVALID')
        $bill_output['code'] = 2;

    $bill_output['billmes'] = "(Transaction Ref: $streference) (ST Confidence: $stconfidence)";

    require $xcart_dir.'/payment/payment_ccmid.php';
    require $xcart_dir.'/payment/payment_ccwebset.php';

} else {

    // Initial redirect to payment gateway

    if (!defined('XCART_START')) { header("Location: ../"); die("Access denied"); }

    $sc_orderids = $module_params['param02'] . join('-', $secure_oid);
    if (!$duplicate)
        db_query("REPLACE INTO $sql_tbl[cc_pp3_data] (ref,sessid,trstat) VALUES ('".addslashes($sc_orderids)."','".$XCARTSESSID."','"."GO|".implode('|', $secure_oid)."')");

    $fields = array(
        'amount' => round($cart["total_cost"] * 100),
        'orderref' => $sc_orderids,
        'orderinfo' => 'Order(s) #' . join(', #', $secure_oid),
        'name' => $bill_name,
        'address' => $userinfo["b_address"],
        'town' => $userinfo["b_city"],
        'county' => $userinfo["b_statename"] ? $userinfo["b_statename"] : "n/a",
        'country' => $userinfo["b_country"],
        'postcode' => $userinfo["b_zipcode"],
        'telephone' => $userinfo["phone"],
        'fax' => $userinfo["fax"],
        'email' => $userinfo["email"],
        'url' => $current_location . '/payment/cc_securetrading.php?return=1&id=' . $sc_orderids,
        'currency' => $module_params["param03"],
        'requiredfields' => 'name,email',
        'merchant' => $module_params["param01"],
        'merchantemail' => $config["Company"]["orders_department"],
        'customeremail' => 1,
        'settlementday' => 1,
        'callbackurl' => 1,
        'failureurl' => 1
    );

    if (!empty($module_params['param04']))
        $fields['formref'] = $module_params["param04"];

    if (defined('SECURETRADING_DEBUG')) {
        func_pp_debug_log('securetrading', 'I', $fields);
    }

    func_create_payment_form('https://securetrading.net/authorize/form.cgi', $fields, 'SECURETRADING.com');
}

exit;

?>
