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
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v15 (xcart_4_7_0), 2015-02-17 23:56:28, func.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!defined('XCART_START')) {
    header('Location: ../../');
    die('Access denied');
}

class XCPPloginErrors { //{{{
    const PROFILE_IS_INCLOMPLETE = 'lbl_paypal_login_cannot_create_user_email';
    const PROFILE_HAS_DUPLICATE = 'lbl_paypal_login_cannot_create_user_email_duplicate';
    
    const AUTH_FAILED = 'txt_paypal_login_auth_failed';
    const EMAIL_EXISTS = 'txt_paypal_login_email_exists';

    public static function getAllCodes() { // {{{
        return array(FALSE, NULL, self::AUTH_FAILED, self::EMAIL_EXISTS, self::PROFILE_IS_INCLOMPLETE, self::PROFILE_HAS_DUPLICATE);
    } // }}}

} //}}} class XCPPloginErrors;

function func_pplogin_init() {

    if (!function_exists('PPSDK_loader')) {

        function PPSDK_loader($class_name) {
            global $xcart_dir;

            static $ppSDK_classes = array(
                'AuthSignature'                  => 'include/lib/paypalSDK/lib/auth/PPAuth.php',
                'FormatterFactory'               => 'include/lib/paypalSDK/lib/formatters/FormatterFactory.php',
                'IPPCredential'                  => 'include/lib/paypalSDK/lib/auth/IPPCredential.php',
                'IPPFormatter'                   => 'include/lib/paypalSDK/lib/formatters/IPPFormatter.php',
                'IPPHandler'                     => 'include/lib/paypalSDK/lib/handlers/IPPHandler.php',
                'IPPThirdPartyAuthorization'     => 'include/lib/paypalSDK/lib/auth/IPPThirdPartyAuthorization.php',
                'MockOAuthDataStore'             => 'include/lib/paypalSDK/lib/auth/AuthUtil.php',
                'OAuthConsumer'                  => 'include/lib/paypalSDK/lib/auth/PPAuth.php',
                'OAuthDataStore'                 => 'include/lib/paypalSDK/lib/auth/PPAuth.php',
                'OAuthException'                 => 'include/lib/paypalSDK/lib/auth/PPAuth.php',
                'OAuthRequest'                   => 'include/lib/paypalSDK/lib/auth/PPAuth.php',
                'OAuthServer'                    => 'include/lib/paypalSDK/lib/auth/PPAuth.php',
                'OAuthSignatureMethod'           => 'include/lib/paypalSDK/lib/auth/PPAuth.php',
                'OAuthSignatureMethod_HMAC_SHA1' => 'include/lib/paypalSDK/lib/auth/PPAuth.php',
                'OAuthSignatureMethod_PLAINTEXT' => 'include/lib/paypalSDK/lib/auth/PPAuth.php',
                'OAuthSignatureMethod_RSA_SHA1'  => 'include/lib/paypalSDK/lib/auth/PPAuth.php',
                'OAuthToken'                     => 'include/lib/paypalSDK/lib/auth/PPAuth.php',
                'OAuthUtil'                      => 'include/lib/paypalSDK/lib/auth/PPAuth.php',
                'PPAPIService'                   => 'include/lib/paypalSDK/lib/PPAPIService.php',
                'PPApiContext'                   => 'include/lib/paypalSDK/lib/common/PPApiContext.php',
                'PPArrayUtil'                    => 'include/lib/paypalSDK/lib/common/PPArrayUtil.php',
                'PPAuthenticationHandler'        => 'include/lib/paypalSDK/lib/handlers/PPAuthenticationHandler.php',
                'PPBaseService'                  => 'include/lib/paypalSDK/lib/PPBaseService.php',
                'PPCertificateAuthHandler'       => 'include/lib/paypalSDK/lib/handlers/PPCertificateAuthHandler.php',
                'PPCertificateCredential'        => 'include/lib/paypalSDK/lib/auth/PPCertificateCredential.php',
                'PPConfigManager'                => 'include/lib/paypalSDK/lib/PPConfigManager.php',
                'PPConfigurationException'       => 'include/lib/paypalSDK/lib/exceptions/PPConfigurationException.php',
                'PPConnectionException'          => 'include/lib/paypalSDK/lib/exceptions/PPConnectionException.php',
                'PPConnectionManager'            => 'include/lib/paypalSDK/lib/PPConnectionManager.php',
                'PPConstants'                    => 'include/lib/paypalSDK/lib/PPConstants.php',
                'PPCredentialManager'            => 'include/lib/paypalSDK/lib/PPCredentialManager.php',
                'PPGenericServiceHandler'        => 'include/lib/paypalSDK/lib/handlers/PPGenericServiceHandler.php',
                'PPHttpConfig'                   => 'include/lib/paypalSDK/lib/PPHttpConfig.php',
                'PPHttpConnection'               => 'include/lib/paypalSDK/lib/PPHttpConnection.php',
                'PPIPNMessage'                   => 'include/lib/paypalSDK/lib/ipn/PPIPNMessage.php',
                'PPInvalidCredentialException'   => 'include/lib/paypalSDK/lib/exceptions/PPInvalidCredentialException.php',
                'PPLoggingLevel'                 => 'include/lib/paypalSDK/lib/PPLoggingLevel.php',
                'PPLoggingManager'               => 'include/lib/paypalSDK/lib/PPLoggingManager.php',
                'PPMerchantServiceHandler'       => 'include/lib/paypalSDK/lib/handlers/PPMerchantServiceHandler.php',
                'PPMessage'                      => 'include/lib/paypalSDK/lib/PPMessage.php',
                'PPMissingCredentialException'   => 'include/lib/paypalSDK/lib/exceptions/PPMissingCredentialException.php',
                'PPModel'                        => 'include/lib/paypalSDK/lib/common/PPModel.php',
                'PPNVPFormatter'                 => 'include/lib/paypalSDK/lib/formatters/PPNVPFormatter.php',
                'PPOpenIdAddress'                => 'include/lib/paypalSDK/lib/auth/openid/PPOpenIdAddress.php',
                'PPOpenIdError'                  => 'include/lib/paypalSDK/lib/auth/openid/PPOpenIdError.php',
                'PPOpenIdHandler'                => 'include/lib/paypalSDK/lib/handlers/PPOpenIdHandler.php',
                'PPOpenIdSession'                => 'include/lib/paypalSDK/lib/auth/openid/PPOpenIdSession.php',
                'PPOpenIdTokeninfo'              => 'include/lib/paypalSDK/lib/auth/openid/PPOpenIdTokeninfo.php',
                'PPOpenIdUserinfo'               => 'include/lib/paypalSDK/lib/auth/openid/PPOpenIdUserinfo.php',
                'PPPlatformServiceHandler'       => 'include/lib/paypalSDK/lib/handlers/PPPlatformServiceHandler.php',
                'PPReflectionUtil'               => 'include/lib/paypalSDK/lib/common/PPReflectionUtil.php',
                'PPRequest'                      => 'include/lib/paypalSDK/lib/PPRequest.php',
                'PPRestCall'                     => 'include/lib/paypalSDK/lib/transport/PPRestCall.php',
                'PPSOAPFormatter'                => 'include/lib/paypalSDK/lib/formatters/PPSOAPFormatter.php',
                'PPSignatureAuthHandler'         => 'include/lib/paypalSDK/lib/handlers/PPSignatureAuthHandler.php',
                'PPSignatureCredential'          => 'include/lib/paypalSDK/lib/auth/PPSignatureCredential.php',
                'PPSubjectAuthorization'         => 'include/lib/paypalSDK/lib/auth/PPSubjectAuthorization.php',
                'PPTokenAuthorization'           => 'include/lib/paypalSDK/lib/auth/PPTokenAuthorization.php',
                'PPTransformerException'         => 'include/lib/paypalSDK/lib/exceptions/PPTransformerException.php',
                'PPUserAgent'                    => 'include/lib/paypalSDK/lib/common/PPUserAgent.php',
                'PPUtils'                        => 'include/lib/paypalSDK/lib/PPUtils.php',
                'PPXmlFaultMessage'              => 'include/lib/paypalSDK/lib/PPXmlFaultMessage.php',
                'PPXmlMessage'                   => 'include/lib/paypalSDK/lib/PPXmlMessage.php',
            );

            if (
                isset($ppSDK_classes[$class_name])
            ) {
                include $xcart_dir . XC_DS . $ppSDK_classes[$class_name];
            }
        }
    }

    spl_autoload_register('PPSDK_loader');
}

function func_get_pplogin_shop_key()
{
    global $config;

    x_load('crypt');

    if (empty($config['pplogin_shop_key'])) {
        $config['pplogin_shop_key'] = func_get_secure_random_key(32);
        func_array2insert(
            'config',
            array(
                'name' => 'pplogin_shop_key',
                'comment' => 'PayPal Login shop key',
                'value' => $config['pplogin_shop_key']
            ),
            true
        );
    }

    return $config['pplogin_shop_key'];
}

function func_pplogin_create_hash($value)
{
    $crypt_salt = func_get_pplogin_shop_key();

    $hash = '';

    if (defined('CRYPT_SHA512')
        && function_exists('crypt')
    ) {
        $hash = crypt($value, '$6$rounds=' . XCSecurity::HASH_CYCLE_LIMIT . '$' . $crypt_salt . '$');

        return $hash;
    }

    for ($i = 0; $i < XCSecurity::HASH_CYCLE_LIMIT; $i++) {
        $hash = hash('sha512', $hash . $crypt_salt . $value);
    }

    return $hash;
}

function func_pplogin_check_user($payerId, $password)
{
    global $sql_tbl, $config;

    $return = array();

    $user = func_query_first(
        'SELECT c.id,p.openid_identity'
        . ' FROM ' . $sql_tbl['customers'] . ' as c'
        . ' INNER JOIN ' . $sql_tbl['lwpp'] . ' as p'
        . ' ON c.id = p.userid AND c.usertype = "C"'
        . ' AND p.payerId = "' . addslashes($payerId) . '"'
        . ' LIMIT 1'
    );

    if ($user) {
        if (text_verify($password . func_get_pplogin_shop_key(), text_decrypt($user['openid_identity']))) {
            $return['userid'] = $user['id'];
            $return['status'] = true;
            $return['error'] = '';
        } else {
            $return['error'] = 'wrong_openid_identity';
            $return['status'] = false;
        }
    } else {
        $return['error'] = 'no_user_data';
        $return['status'] = false;
    }

    return $return;
}

function func_pplogin_create_user($paypal_profile, $address)
{
    global $config, $mail_smarty, $shop_language, $active_modules, $sql_tbl, $xcart_dir;

    if (!func_pplogin_is_profile_completed($paypal_profile)) {
        x_log_add(
            'paypal_login',
            func_get_langvar_by_name(XCPPloginErrors::PROFILE_IS_INCLOMPLETE, null, false, true, true)
        );

        return XCPPloginErrors::PROFILE_IS_INCLOMPLETE;
    }

    x_load('crypt',
    'user'); // For XCUserSql

    $xcart_profile = array();

    $xcart_profile['username'] = isset($paypal_profile['username']) ? $paypal_profile['username'] : $paypal_profile['email'];
    $xcart_profile['email'] = $paypal_profile['email'];
    $xcart_profile['firstname'] = $paypal_profile['firstname'];
    $xcart_profile['lastname'] = $paypal_profile['lastname'];

    $xcart_profile['login']    = 'Y' == $config['email_as_login'] ? $xcart_profile['email'] : $xcart_profile['username'];
    $xcart_profile['usertype'] = 'C';
    $xcart_profile['language'] = $shop_language;
    $xcart_profile['password'] = func_get_secure_random_key(32);
    $xcart_profile['status']   = 'Y';
    $xcart_profile['change_password_date'] = 0;

    $xcart_profile = func_addslashes($xcart_profile);

    // Check email + usertype unique
    $userIsExists = func_query_first_cell(
        'SELECT COUNT(email) FROM ' . $sql_tbl['customers']
        . ' WHERE email = "' . $xcart_profile['email'] . '"'
        . ' AND usertype = "' . $xcart_profile['usertype'] . '"'
        . ' AND ' . XCUserSql::getSqlRegisteredCond()
    );

    if (0 < $userIsExists) {
        x_log_add(
            'paypal_login',
            func_get_langvar_by_name(
                XCPPloginErrors::PROFILE_HAS_DUPLICATE,
                null,
                false,
                true,
                true
            )
        );

        return XCPPloginErrors::EMAIL_EXISTS;
    }

    // Check login unique
    $userIsExists = func_query_first_cell('SELECT COUNT(login) FROM ' . $sql_tbl['customers'] . ' WHERE login = "' . $xcart_profile['login'] . '" AND ' . XCUserSql::getSqlRegisteredCond());

    if (0 < $userIsExists) {
        x_log_add(
            'paypal_login',
            func_get_langvar_by_name(
                XCPPloginErrors::PROFILE_HAS_DUPLICATE,
                null,
                false,
                true,
                true
                )
        );

        return XCPPloginErrors::EMAIL_EXISTS;
    }

    // Create user
    $newuserid = func_array2insert('customers', $xcart_profile);

    $query_data = array(
        'userid' => $newuserid,
        'payerId' => $paypal_profile['payerid'],
        'openid_identity' => addslashes(text_crypt(text_hash($paypal_profile['openid_identity'] . func_get_pplogin_shop_key()))),
        'pplogin_email' => $xcart_profile['email'],
    );

    // Insert link to external auth id
    func_array2insert('lwpp', $query_data, true);

    // Add address
    if ($address) {
        $address['userid'] = $newuserid;
        $address['default_s'] = 'Y';
        $address['default_b'] = 'Y';

        $address = func_addslashes($address);

        $result = func_check_address($address, $xcart_profile['usertype']);
        if (empty($result['errors'])) {
            func_save_address($newuserid, 0, $address);
        } else {
            x_log_add(
                'paypal_login',
                func_get_langvar_by_name(
                    XCPPloginErrors::PROFILE_IS_INCLOMPLETE,
                    null,
                    false,
                    true,
                    true
                )
            );
        }
    }

    // Email notifications
    x_load('mail');

    $newuser_info = func_userinfo($newuserid, $xcart_profile['usertype'], false, NULL, 'C', false);
    $mail_smarty->assign('userinfo', $newuser_info);
    $mail_smarty->assign('full_usertype', func_get_langvar_by_name('lbl_customer'));
    $to_customer = $newuser_info['language'];

    // Send mail to customer
    if ('Y' == $config['Email_Note']['eml_signin_notif']) {
        func_send_mail(
            $newuser_info['email'],
            'mail/signin_notification_subj.tpl',
            'mail/signin_notification.tpl',
            $config['Company']['users_department'],
            false
        );
    }

    // Send mail to customers department
    if ('Y' == $config['Email_Note']['eml_signin_notif_admin']) {
        func_send_mail(
            $config['Company']['users_department'],
            'mail/signin_admin_notif_subj.tpl',
            'mail/signin_admin_notification.tpl',
            $xcart_profile['email'],
            true
        );
    }

    require_once $xcart_dir . '/include/classes/class.XCSignature.php';
    $obj = new XCUserSignature($newuser_info);
    $obj->updateSignature();

    return $newuserid;
}

function func_pplogin_login_user($userid)
{
    x_load('user');

    func_authenticate_user($userid);
}

function func_pplogin_is_profile_completed($paypal_profile)
{
    x_load('user');

    $completed = false;

    if (is_array($paypal_profile)) {

        $additional_fields = func_get_additional_fields('C', 0);
        $default_fields = func_get_default_fields('C');

        $completed = true;

        foreach ($default_fields as $k => $v) {
            if (
                    'Y' == $v['required']
                    && (!isset($paypal_profile[$k]) || empty($paypal_profile[$k]))
               ) {
                $completed = false;
                break;
            }
        }

        if ($additional_fields && $completed) {
            foreach ($additional_fields as $v) {
                if ('Y' == $v['required']) {
                    $completed = false;
                    break;
                }
            }
        }

        $completed = $completed
            && isset($paypal_profile['email'])
            && is_string($paypal_profile['email'])
            && preg_match('/' . func_email_validation_regexp() . '/Ss', $paypal_profile['email']);

    }

    return $completed;
}

/**
 * Close popup window and refresh opener window
 */
function func_pplogin_close_pp_window()
{
?>
<script type="text/javascript">
//<![CDATA[
/* CMD: refresh_window_parent */
    window.opener.location.reload();
    window.close();
//]]>
</script>
<?php
    exit;
}

?>
