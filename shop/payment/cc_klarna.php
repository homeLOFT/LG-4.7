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
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v6 (xcart_4_7_0), 2015-02-17 23:56:28, cc_klarna.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!isset($REQUEST_METHOD)) {

    $REQUEST_METHOD = $_SERVER['REQUEST_METHOD'];
}

if ($REQUEST_METHOD == 'POST' && !empty($_POST['tid']) && isset($_POST['status']) && !empty($_POST['klarna_response'])) {

    require __DIR__.'/auth.php';
    require_once $xcart_dir . '/modules/Klarna_Payments/postinit.php';
    
    x_session_register('cart');

    $bill_output['sessid'] = func_query_first_cell("SELECT sessid FROM $sql_tbl[cc_pp3_data] WHERE ref='$tid'");
    if ($status == KlarnaFlags::ACCEPTED || $status == KlarnaFlags::PENDING) {

        $bill_output['code'] = 1;
        $bill_output['is_preauth'] = true;
        $extra_order_data = array(
            'reservation_id' => $klarna_response,
            'capture_status' => 'A',
        );
            
        $order_ids = explode('-', $tid);
        $k_status = ($status == KlarnaFlags::ACCEPTED) ? 'A' : 'P';
        db_query("UPDATE $sql_tbl[orders] SET klarna_order_status = '$k_status' WHERE orderid IN ('" . implode("','", $order_ids) . "')");

    } else {

        $bill_output['code'] = 2;
        $bill_output['billmes'] = 'Transaction error: ' . $klarna_response;
    }
    require($xcart_dir . '/payment/payment_ccend.php');

} else {

    if (!defined('XCART_SESSION_START')) {
        header('Location: ../');
        die('Access denied');
    }

    require_once $xcart_dir . '/modules/Klarna_Payments/postinit.php';
   
    if (!empty($userinfo) && !empty($cart['klarna_address']) && $cart['use_klarna_address'] == 'Y') {
        
        $userinfo['address']['B'] = func_array_merge($userinfo['address']['B'], $cart['klarna_address']);
        $userinfo['address']['S'] = func_array_merge($userinfo['address']['S'], $cart['klarna_address']);

        foreach ($cart['klarna_address'] as $k => $v) {
            
            $userinfo['b_'.$k] = $cart['klarna_address'][$k];
            $userinfo['s_'.$k] = $cart['klarna_address'][$k];
        }
    }

    $module_params = func_query_first("SELECT * FROM $sql_tbl[ccprocessors] WHERE paymentid='$paymentid'");
    $order_prefix = ($module_params['processor'] == 'cc_klarna.php') ? $config['Klarna_Payments']['klarna_invoice_order_prefix'] : $config['Klarna_Payments']['klarna_pp_order_prefix'];
    $_orderids = $order_prefix . join('-', $secure_oid);

    $tid = join('-', $secure_oid);

    db_query("REPLACE INTO $sql_tbl[cc_pp3_data] (ref,sessid,trstat) VALUES ('" . addslashes($tid) . "', '" . $XCARTSESSID . "','GO|" . implode('|',$secure_oid) . "')");
    
    if ($module_params['processor'] == 'cc_klarna.php') {
        $selected_pclass = KlarnaPClass::INVOICE;
    }
    

    list($payment_country, $payment_language, $payment_currency) = func_klarna_get_location_codes($config['Klarna_Payments']['user_country']);

    if (empty($payment_country)) {
        
        x_session_register('top_message');
        $top_message = array(
            'type'    => 'E',
            'content' => func_get_langvar_by_name('lbl_klarna_country_not_supported', false, false, true)
        );
        func_header_location('../cart.php?mode=checkout');

    }
   
    $payment_currency_rate = 1;
    if (strtolower($payment_currency) != KlarnaCurrency::getCode($config['Klarna_Payments']['klarna_default_store_currency'])) {
        $currency_avail = func_klarna_check_currency_avail($payment_currency);

        if (!$currency_avail) {
            
            x_session_register('top_message');
            $top_message = array(
                'type'    => 'E',
                'content' => func_get_langvar_by_name('lbl_klarna_currency_err', false, false, true)
            );
            func_header_location('../cart.php?mode=checkout');

        } else {
            
            $customer_currency = func_mc_get_currency($payment_currency);
            
            $payment_currency_rate = (isset($customer_currency['rate']) && $customer_currency['is_default'] < 1 && 0 < doubleval($customer_currency['rate']) ? $customer_currency['rate'] : 1);
            $payment_currency = KlarnaCurrency::fromCode(strtolower($payment_currency));
        }
    }

    $k = new Klarna();

    func_klarna_create_config($k, ($module_params['processor'] == 'cc_klarna.php') ? 'invoice' : 'part_payment', KlarnaCountry::getCode($payment_country));

    Klarna::$xmlrpcDebug = false;  
    Klarna::$debug = false;

    /*Add products to the goods list*/
    
    func_klarna_create_goods_list($k, $cart['products'], $payment_currency_rate);

    if ($cart['shipping_cost'] > 0) {

        func_klarna_add_shipping_cost($k, $cart['shipping_cost'] * $payment_currency_rate, $cart['taxes']);
    }

    if ($cart['payment_surcharge'] > 0) {
        
        func_klarna_add_payment_surcharge($k, $cart['payment_surcharge'] * $payment_currency_rate, $cart['taxes']);
    }
    
    if (!empty($cart['coupon']) && $cart['coupon'] > 0) {
        
        func_klarna_add_coupon($k, $cart, $payment_currency_rate);
    }

    func_klarna_add_addresses($k, $userinfo, $payment_country);
   
    $k->setEstoreInfo(
        $orderid1 = $_orderids, //Maybe the estore's order number/id.
        $orderid2 = '', //Could an order number from another system?
        $user = $userinfo['id'] //Username, email or identifier for the user?
    );
    
    try {
        $result = $k->reserveAmount(
            $pno = $userinfo['ssn'], //Date of birth for DE.
            $gender = ($userinfo['user_gender'] !== false) ? intval($userinfo['user_gender']) : null, //The customer is a male.
            $amount = -1,
            $flags = KlarnaFlags::NO_FLAG, //No specific behaviour like RETURN_OCR or TEST_MODE.
            $pclass = (($selected_pclass) ? $selected_pclass : KlarnaPClass::INVOICE) //-1, notes that this is an invoice purchase, for part payment purchase you will have a pclass object on which you use getId().
        );
    } catch (Exception $e) {
        $result[0] = $e->getMessage();
        $result[1] = 0;
        $result[2] = $e->getCode();
    }
    

    ?>
    <form action="<?php print($current_location . '/payment/cc_klarna.php'); ?>" method="post" name="klarna_form" id="klarna_form">
        <input type="hidden" name="status" value="<?php print($result[1]); ?>" />
        <input type="hidden" name="klarna_response" value="<?php print(func_convert_encoding($result[0], 'ISO-8859-1', 'UTF-8')); ?>" />
        <input type="hidden" name="tid" value="<?php print($tid); ?>" />
        <input type="hidden" name="error_code" value="<?php print($result[2]); ?>" />
    </form>
    <script type="text/javascript">
    //<![CDATA[
        document.getElementById('klarna_form').submit();
    //]]>    
    </script>
    <?php
}
exit;
?>
