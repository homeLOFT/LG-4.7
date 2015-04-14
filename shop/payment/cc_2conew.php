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
 * 2Checkout.com
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Payment interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v63 (xcart_4_7_0), 2015-02-17 13:29:01, cc_2conew.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!empty($_POST['cart_order_id']) || !empty($_GET['cart_order_id'])) {
    require __DIR__.'/auth.php';

    $key = $_POST['key'];
    $tmp = func_query_first("SELECT sessid,param1 FROM $sql_tbl[cc_pp3_data] WHERE ref='$cart_order_id'");
    $tmp['param1'] = price_format($tmp['param1']);

    $bill_output['sessid'] = $tmp['sessid'];
    $s = func_query_first("select param01,param03,testmode from $sql_tbl[ccprocessors] where processor='cc_2conew.php'");

    $bill_output['code'] = ($credit_card_processed=="Y" ? 1 : ($credit_card_processed=="K" ? 3 : 2));
    $order_ = ($s['testmode']=="Y") ? 1 : $order_number;
    if (
        $total != $tmp['param1'] 
        || strtoupper(md5($s['param03'].$s['param01'].$order_.$tmp['param1'])) != $key
    ) {
        $bill_output['code'] = 2;
        $bill_output['billmes'] = "MD5 HASH is invalid!";

    } else {
        $bill_output['billmes'] = '';
    }

    if (!empty($order_number))
        $bill_output['billmes'].= " (Order number: ".$order_number.")";

    if (!empty($tcoid))
        $bill_output['billmes'].= " (TransID: ".$tcoid.") ";

    // Make IE happy with 2Checkout response for some server configurations
    echo str_repeat(" ", 600);

    // Save the full response in the order details
    $response_vars = '_' . $_SERVER['REQUEST_METHOD'];
    if (is_array($GLOBALS[$response_vars])) {
        foreach ($GLOBALS[$response_vars] as $k=>$v) {
            $full_response[] = "$k=>$v";
        }
        $bill_output['billmes'] .= "\n-------------------------\nFull response:\n";
        $bill_output['billmes'] .= implode("\n", $full_response);
        $bill_output['billmes'] .= "\n-------------------------\n";
    }

    // Force redirecting back to the site
    $weblink = 2;

    define ('NOCOOKIE', 1);

    require($xcart_dir.'/payment/payment_ccend.php');

} else {

    if (!defined('XCART_START')) { header("Location: ../"); die("Access denied"); }

    $_orderids = $module_params['param02'] . join('-', $secure_oid);
    $co_lang =  empty($module_params['param05']) ? 'en' : $module_params['param05'];
    $url = $module_params['param06'];

    if (!$duplicate)
        db_query("REPLACE INTO $sql_tbl[cc_pp3_data] (ref,sessid,param1) VALUES ('".addslashes($_orderids)."','".$XCARTSESSID."','".(float)price_format($cart["total_cost"])."')");

    $b_state = $userinfo['b_state'];
    if (empty($b_state)) {
        $b_state = 'n/a';

    } elseif (!in_array($userinfo['b_country'], array("US","CA"))) {
        $b_state = 'XX';
    }

    $s_state = $userinfo['s_state'];
    if (empty($s_state)) {
        $s_state = 'n/a';

    } elseif (!in_array($userinfo['s_country'], array("US","CA"))) {
        $s_state = 'XX';
    }

    $fields = array(
        'sid' => $module_params["param01"],
        'total' => (float)price_format($cart["total_cost"]),
        'cart_order_id' => $_orderids,
        'merchant_order_id' => $_orderids,
        'pay_method' => 'CC',
        'lang' => $co_lang,
        'skip_landing' => $module_params["param04"] == "Y" ? "1" : "",
        'card_holder_name' => $bill_name,
        'street_address' => $userinfo["b_address"],
        'street_address2' => $userinfo["b_address_2"],
        'city' => $userinfo["b_city"],
        'state' => $b_state,
        'zip' => $userinfo["b_zipcode"],
        'country' => $userinfo["b_country"],
        'email' => $userinfo["email"],
        'phone' => $userinfo["phone"],
        'ship_name' => $userinfo["s_firstname"] . ' ' . $userinfo["s_lastname"],
        'ship_street_address' => $userinfo["s_address"],
        'ship_street_address2' => $userinfo["s_address_2"],
        'ship_city' => $userinfo["s_city"],
        'ship_state' => $s_state,
        'ship_zip' => $userinfo["s_zipcode"],
        'ship_country' => $userinfo["s_country"],
        'fixed' => 'Y',
        'id_type' => '1',
        'sh_cost' => price_format($cart["shipping_cost"])
    );

    if ($module_params['testmode'] == 'Y')
         $fields['demo'] = 'Y';

    $i = -1;
    if (!empty($products)) {
        foreach ($products as $v) {
            $i++;
            if (empty($v['descr']))
                $v['descr'] = func_query_first_cell("SELECT descr FROM $sql_tbl[products_lng_current] WHERE productid = '$v[productid]'");

            $suffix = $i == 0 ? '' : ('_' . $i);

            $fields['c_prod' . $suffix] = $v['productid'].",".$v['amount'];
            $fields['c_name' . $suffix] = substr($v['product'], 0, 127);
            $fields['c_price' . $suffix] = price_format($v['price']);
            $fields['c_description' . $suffix] = strip_tags(substr($v['descr'] ? $v['descr'] : $v['product'], 0, 254));
            $fields['c_tangible' . $suffix] = empty($v['distribution']) ? 'Y' : 'N';
        }
    }

    if (!empty($cart['giftcerts'])) {
        foreach ($cart['giftcerts'] as $v) {
            $i++;

            $suffix = $i == 0 ? '' : ('_' . $i);

            $fields['c_prod_' . $suffix] = $i . ",1";
            $fields['c_name_' . $suffix] = 'Gift certificate';
            $fields['c_price_' . $suffix] = price_format($v['amount']);
            $fields['c_description_' . $suffix] = 'Gift certificate';
            $fields['c_tangible_' . $suffix] = 'N';
        }
    }

    func_create_payment_form($url, $fields, '2checkout.com');
}

exit;

?>
