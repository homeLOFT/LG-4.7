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
 * PayPal Website Payments Pro (2.0 version; for UK and US)
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Payment interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v63 (xcart_4_7_0), 2015-02-17 23:56:28, ps_paypal_pro_uk.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header("Location: ../"); die("Access denied"); }

$pp_customer_url = ($pp_test == 'N') ? 'https://www.paypal.com' : 'https://www.sandbox.paypal.com';

$avs_codes = array (
    'Y' => 'Match',
    'N' => "No match",
    'X' => "The cardholders bank does not support this service"
);

$cvv_codes = array (
    'Y' => 'Match',
    'N' => "No match",
    'X' => "The cardholders bank does not support this service"
);

if ($REQUEST_METHOD == 'GET' && $mode == 'express') {
    // start express checkout

    x_session_register('paypal_begin_express');
    $paypal_begin_express = false;
    if (defined('XC_SESSION_DB_SAVE_PAYPAL_EXPRESS')) {
        x_session_save('paypal_begin_express');
    }

    x_session_register('paypal_payment_id');
    x_session_register('paypal_mode');

    $paypal_payment_id = $payment_id;
    $paypal_mode = 'express';

    x_load('user');
    $userinfo = func_userinfo($logged_userid, 'C');
    if (!func_is_completed_userinfo($userinfo)) {
        $userinfo = array();
    }
    $result = func_paypal_sec_uk($paypal_payment_id, $cart, $userinfo, !empty($useraction) && $useraction == 'commit', (!empty($active_modules['Bill_Me_Later']) && !empty($bml_enabled)));

    if (!empty($useraction) && $useraction == 'commit') {
        $result['redirect_url'] .= '&useraction=' . $useraction;
    }


    if ($result['status'] == 'error') {
        $top_message = array(
            'type' => 'E',
            'content' => $result['error']
        );
        func_header_location($xcart_catalogs['customer'].'/cart.php?mode=checkout');
    }

    x_session_register('paypal_token_ttl');
    $paypal_token_ttl = XC_TIME;

    // move to the PayPal
    func_header_location($result['redirect_url']);

} elseif ($REQUEST_METHOD == 'GET' && !empty($_GET['token'])) {
    // return from PayPal
    // send GetExpressCheckoutDetailsRequest

    x_session_register('paypal_payment_id');

    $result = func_paypal_gec_uk($paypal_payment_id, $_GET['token']);

    if ($result['status'] == 'error') {
        $top_message = array(
            'type' => 'E',
            'content' => $result['error']
        );
        func_header_location($xcart_catalogs['customer'].'/cart.php?mode=checkout');
    }

    $state_err = 0;


    x_session_register('login');
    x_session_register('login_type');
    x_session_register('logged_userid');

    x_session_register('cart');

    if (empty($useraction) || $useraction != 'commit') {

        $shiptoname = explode(' ', $result['shiptoname']);

        $address = array (
            'firstname' => (!empty($shiptoname[0])) ? $shiptoname[0] : $result['firstname'],
            'lastname'  => (!empty($shiptoname[1])) ? $shiptoname[1] : (empty($shiptoname[0]) ? $result['lastname'] : ''),
            'address' => preg_replace('![\s\n\r]+!s', ' ', $result['shiptostreet']),
            'city' => $result['shiptocity'],
            'state' => func_paypal_detect_state($result['shiptocountry'], $result['shiptostate'], $result['shiptozip'], $state_err),
            'country' => $result['shiptocountry'],
            'zipcode' => $result['shiptozip'],
            'phone' => $result['phonenum']
        );

        if (!empty($result['shiptostreet2']))
            $address['address'] .= "\n" . $result['shiptostreet2'];

        if ($config['General']['use_counties'] == 'Y') {
            $default_county = func_default_county($address['state'], $address['country']);
            $address['county'] = empty($default_county) ? $result['shiptostate'] : $default_county;
        }

        $parsed_profile = array();
        $parsed_profile['firstname'] = $result['firstname'];
        $parsed_profile['lastname'] = $result['lastname'];
        $parsed_profile['phone'] = $result['phonenum'];
        $parsed_profile['email'] = $result['email'];
        $parsed_profile['referer'] = !empty($RefererCookie) ? $RefererCookie : '';
        
        func_paypal_save_profile($address, $parsed_profile);

    }

    if ((empty($login) || $login_type != 'C') && $config['General']['enable_anonymous_checkout'] != 'Y') {
        // Display a warning message about expired session
        $top_message = array(
            'type' => 'E',
            'content' => func_get_langvar_by_name('txt_paypal_expired_session_warn')
        );
        func_header_location($xcart_catalogs['customer'] . '/login.php');
    }

    x_session_register('paypal_token');
    x_session_register('paypal_express_details');
    $paypal_token = $result['token'];
    $paypal_express_details = $result;

    switch ($state_err) {
        case 1:
            $top_message = array(
                'type' => 'W',
                'content' => func_get_langvar_by_name('lbl_paypal_wrong_country_note')
            );
            break;

        case 2:
        case 3:
            $top_message = array(
                'type' => 'W',
                'content' => func_get_langvar_by_name('lbl_paypal_wrong_state_note')
            );
    }
    $profile_add = '';
    $paymentid_add = "&paymentid=$paypal_payment_id";

    if (!empty($state_err)) {
        $profile_add = '&edit_profile';
        $paymentid_add = ($checkout_module == 'Fast_Lane_Checkout' ? '' : $paymentid_add);
    }

    if (!empty($useraction) && $useraction == 'commit') {
            // Skip Order Confirmation page and submit order
            x_session_register('PPEC_POST_VARS');
?>

<html>
<body onLoad="document.process.submit();">
  <form action="<?php echo $current_location.'/payment/payment_cc.php'; ?>" method="POST" name="process">
<?php foreach ($PPEC_POST_VARS as $k => $v) { ?>
    <input type="hidden" name="<?php echo $k; ?>" value="<?php echo $v; ?>">
<?php } ?>
  </form>
</body>
</html>

<?php
        exit;
    } else {
        func_header_location($xcart_catalogs['customer'] . '/cart.php?mode=checkout' . $paymentid_add . $profile_add);
    }

} elseif ($REQUEST_METHOD == 'POST' && $_POST["action"] == 'place_order') {

    // Finisn ExpressCheckout

    db_query("REPLACE INTO $sql_tbl[cc_pp3_data] (ref,sessid,trstat) VALUES ('".addslashes($order_secureid)."','".$XCARTSESSID."','GO|".implode('|',$secure_oid)."')");

    x_session_register('paypal_express_details');

    $result = func_paypal_dec_uk(
        $paymentid,
        $paypal_express_details['token'],
        $paypal_express_details['payerid'],
        $cart,
        $userinfo,
        join("-",$secure_oid),
        $order_secureid
    );

    if ($result['status'] == 'success') {
        $bill_output['code'] = 1;
        $bill_message = 'Accepted ('.$result['respmsg'].')';
    } else {
        $bill_output['code'] = 2;
        $bill_output['hide_mess'] = true;
        $bill_message = "Reason: ".$result['error'];

        if (isset($result['error_code']) && in_array($result['error_code'], array('12','22','23','24'))) {
            $bill_output['hide_mess'] = false;
        } else {
            $bill_output['is_error'] = true;
        }

    }

    $bill_output['billmes'] = $bill_message;

    $bill_output['avsmes'] = '';

    if (isset($result['avsaddr']))
        $bill_output['avsmes'] .= "AVS address: ".(empty($avs_codes[$result['avsaddr']]) ? "Code: ".$result['avsaddr'] : $avs_codes[$result['avsaddr']])."; ";

    if (isset($result['avszip']))
        $bill_output['avsmes'] .= "AVS zipcode: ".(empty($avs_codes[$result['avszip']]) ? "Code: ".$result['avszip'] : $avs_codes[$result['avszip']])."; ";

    if (isset($result['cvv2match']))
        $bill_output['cvvmes'] = (empty($cvv_codes[$result['cvv2match']]) ? "Code: ".$result['cvv2match'] : $cvv_codes[$result['cvv2match']]);

    if ($pp_final_action != 'Sale')
        $bill_output['is_preauth'] = true;

    $extra_order_data = array(
        'ppref' => $result['ppref'],
        'pnref' => $result['pnref'],
        'paypal_type' => 'UKEC',
        'capture_status' => $pp_final_action != 'Sale' ? 'A' : ''
    );

    x_load('cart');
    func_paypal_express_enable_1step();
}
?>
