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
 * Login with PayPal
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v10 (xcart_4_7_0), 2015-02-17 23:56:28, pp_return.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!defined('XCART_START')) {
    header('Location: ../../');
    die('Access denied');
}

if (defined('PAYPAL_LOGIN_DEBUG')) {
    x_log_add('paypal_login', print_r($_GET, true));
}

if (
    !empty($error)
    && !empty($error_description)
) {
    $top_message['content'] = $error_description;
    $top_message['type']    = 'E';

    func_pplogin_close_pp_window();
}

$apicontext = new PPApiContext(array('mode' => $config['PayPal_Login']['paypal_login_test_mode'] == 'Y' ? 'sandbox' : 'live'));

$clientId = $config['PayPal_Login']['paypal_login_client_id'];
$clientSecret = $config['PayPal_Login']['paypal_login_secret'];

$params = array(
    'client_id' => $clientId,
    'client_secret' => $clientSecret,
    'code' => $code
);

$token = PPOpenIdTokeninfo::createFromAuthorizationCode($params, $apicontext);

if (defined('PAYPAL_LOGIN_DEBUG')) {
    x_log_add('paypal_login', print_r($token, true));
}

$params = array(
    'access_token' => $token->getAccessToken(),
);

$user = PPOpenIdUserinfo::getUserinfo($params, $apicontext);

if (defined('PAYPAL_LOGIN_DEBUG')) {
    x_log_add('paypal_login', print_r($user, true));
}

$_pplogin_profile = array(
    'payerid' => func_pplogin_create_hash($user->getUserId()),
    'email' => $user->getEmail(),
    'firstname' => $user->getGivenName(),
    'lastname' => $user->getFamilyName(),
    'openid_identity' => $user->getUserId()
);

$_pplogin_address = array(
    'firstname' => $user->getGivenName(),
    'lastname' => $user->getFamilyName(),
    'zipcode' => $user->getAddress()->getPostalCode(),
    'country' => $user->getAddress()->getCountry(),
    'address' => $user->getAddress()->getStreetAddress(),
    'city' => $user->getAddress()->getLocality(),
    'state' => $user->getAddress()->getRegion(),
    'phone' => $user->getPhoneNumber(),
);

x_load('paypal');

$_pplogin_state_error = 0;

// detect state from info returned by PayPal
$_pplogin_address['state'] = func_paypal_detect_state(
    $_pplogin_address['country'],
    $_pplogin_address['state'],
    $_pplogin_address['zipcode'],
    $_pplogin_state_error
);

$_pplogin_check_result = func_pplogin_check_user($_pplogin_profile['payerid'], $_pplogin_profile['openid_identity']);

if (defined('PAYPAL_LOGIN_DEBUG')) {
    x_log_add('paypal_login', print_r($_pplogin_check_result, true));
}

if ($_pplogin_check_result['error'] == 'no_user_data') {

    $_pplogin_userid = func_pplogin_create_user($_pplogin_profile, $_pplogin_address);
    $_pplogin_check_result['status'] = true;

} elseif ($_pplogin_check_result['status'] == true) {

    $_pplogin_userid = $_pplogin_check_result['userid'];

}

if (
    !empty($_pplogin_userid)
    && isset($_pplogin_check_result['status'])
    && $_pplogin_check_result['status'] == true
) {
    // check if we managed to create a new user
    if (!in_array($_pplogin_userid, XCPPloginErrors::getAllCodes())) {
        
        func_pplogin_login_user($_pplogin_userid);
        
    } else {
        // failed to create a user, show error
        $top_message['content'] = func_get_langvar_by_name($_pplogin_userid, null, false, true, true);
        $top_message['type']    = 'E';
    }

} else {

    $top_message['content'] = func_get_langvar_by_name(XCPPloginErrors::AUTH_FAILED, null, false, true, true);
    $top_message['type']    = 'E';

}

func_pplogin_close_pp_window();

?>
